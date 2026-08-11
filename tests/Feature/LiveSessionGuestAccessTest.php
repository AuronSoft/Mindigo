<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionGuest;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Services\LiveSessionGuestService;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Tests\TestCase;

final class LiveSessionGuestAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_creates_hashed_limited_link_and_guest_enters_waiting_room(): void
    {
        [$session, $teacher] = $this->room();
        $response = $this->actingAs($teacher)->post(route('teacher.live-sessions.guest-links.store', $session), ['ttl_minutes' => 60, 'max_uses' => 1]);
        $response->assertRedirect()->assertSessionHas('guest_link_url');
        $url = session('guest_link_url');
        $token = basename(parse_url($url, PHP_URL_PATH));
        $this->assertDatabaseMissing('live_session_guest_links', ['token_hash' => $token]);
        $this->assertDatabaseHas('live_session_guest_links', ['token_hash' => hash('sha256', $token), 'max_uses' => 1]);

        $this->get($url)->assertOk()->assertSee($session->title);
        $this->post(route('live-guest.join', $token), ['name' => 'External Parent', 'email' => 'parent@example.com'])
            ->assertRedirect();
        $guest = LiveSessionGuest::query()->sole();
        $this->assertSame(ParticipantAdmissionStatus::Waiting, $guest->admission_status);
        $this->assertDatabaseHas('live_session_guest_links', ['id' => $guest->guest_link_id, 'uses_count' => 1]);
        $this->get(route('live-guest.show', $token))->assertGone();
    }

    public function test_moderator_admits_guest_and_guest_exchanges_media_signals_with_teacher(): void
    {
        [$session, $teacher] = $this->room();
        $service = app(LiveSessionGuestService::class);
        $link = $service->createLink($session, $teacher, 60, null)['link'];
        $registration = $service->register($link, 'Guest Speaker', 'speaker@example.com');
        $guest = $registration['guest'];
        $this->actingAs($teacher)->post(route('teacher.live-sessions.guests.decision', [$session, $guest]), ['decision' => 'admitted'])->assertRedirect();

        $teacherParticipant = LiveSessionParticipant::query()->create([
            'live_session_id' => $session->id, 'user_id' => $teacher->id, 'role' => LiveParticipantRole::Host,
            'admission_status' => ParticipantAdmissionStatus::Admitted, 'admitted_at' => now(), 'last_seen_at' => now(),
        ]);
        $teacherToken = app(LiveSessionJoinTokenService::class)->issue($session, $teacher, LiveParticipantRole::Host);
        $guestToken = $registration['access_token'];

        $this->postJson(route('live-guest-media.presence', [$session, $guest]), [
            'token' => $guestToken, 'connection_id' => 'guest-browser', 'microphone_enabled' => true,
        ])->assertOk()->assertJsonFragment(['key' => 'guest:'.$guest->id, 'name' => 'Guest Speaker']);
        $this->postJson(route('live-guest-media.signals.store', [$session, $guest]), [
            'token' => $guestToken, 'recipient_key' => 'user:'.$teacher->id,
            'type' => 'offer', 'payload' => ['type' => 'offer', 'sdp' => 'guest-offer'],
        ])->assertAccepted();
        $this->actingAs($teacher)->postJson(route('live-media.signals.inbox', $session), ['token' => $teacherToken])
            ->assertOk()->assertJsonPath('signals.0.sender_key', 'guest:'.$guest->id);
        $this->actingAs($teacher)->postJson(route('live-media.signals.store', $session), [
            'token' => $teacherToken, 'recipient_key' => 'guest:'.$guest->id,
            'type' => 'answer', 'payload' => ['type' => 'answer', 'sdp' => 'teacher-answer'],
        ])->assertAccepted();
        $this->postJson(route('live-guest-media.signals.inbox', [$session, $guest]), ['token' => $guestToken])
            ->assertOk()->assertJsonPath('signals.0.sender_key', 'user:'.$teacher->id);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.guests.decision', [$session, $guest]), ['decision' => 'removed'])->assertRedirect();
        $this->postJson(route('live-guest-media.presence', [$session, $guest]), [
            'token' => $guestToken, 'connection_id' => 'removed-browser',
        ])->assertForbidden();
    }

    public function test_disabled_or_revoked_guest_access_is_rejected(): void
    {
        [$session, $teacher] = $this->room(['room_settings' => ['guest_access_enabled' => false]]);
        $this->actingAs($teacher)->post(route('teacher.live-sessions.guest-links.store', $session), ['ttl_minutes' => 60])->assertUnprocessable();

        $session->update(['room_settings' => ['guest_access_enabled' => true]]);
        $service = app(LiveSessionGuestService::class);
        $result = $service->createLink($session, $teacher, 60, null);
        $service->revokeLink($result['link'], $teacher);
        $token = basename(parse_url($result['url'], PHP_URL_PATH));
        $this->get(route('live-guest.show', $token))->assertGone();
    }

    private function room(array $attributes = []): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Guest class', 'code' => 'GST-'.uniqid(), 'slug' => 'gst-'.uniqid(), 'status' => 'active',
        ]);
        $session = LiveSession::query()->create([
            'classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id,
            'title' => 'Guest lesson', 'room_name' => 'guest-'.uniqid(), 'provider' => 'native',
            'provider_status' => 'live', 'fallback_provider' => 'native', 'sync_status' => 'not_required',
            'session_type' => 'flexible', 'room_settings' => ['guest_access_enabled' => true],
            'scheduled_start' => now()->subMinute(), 'scheduled_end' => now()->addHour(), 'status' => 'live', ...$attributes,
        ]);

        return [$session, $teacher];
    }
}
