<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Enums\ProviderSyncStatus;
use Mindigo\TeacherLiveSession\Models\LiveProviderConnection;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionRoomEvent;
use Mindigo\TeacherLiveSession\Models\LiveSessionSignal;
use Mindigo\TeacherLiveSession\Services\LiveProviderSyncService;
use Mindigo\TeacherLiveSession\Services\LiveProviderTokenService;
use Tests\TestCase;

class LiveSessionProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_room_authorizes_and_tracks_participant_before_redirecting(): void
    {
        [$teacher, $classroom] = $this->context();
        $session = $this->liveSession($teacher->id, $classroom->id, ['provider' => 'google_meet', 'provider_join_url' => 'https://meet.google.com/abc-defg-hij', 'provider_host_url' => 'https://meet.google.com/abc-defg-hij']);

        $this->actingAs($teacher)->get(route('teacher.live-sessions.room', $session))
            ->assertRedirect('https://meet.google.com/abc-defg-hij');

        $this->assertDatabaseHas('live_session_participants', ['live_session_id' => $session->id, 'user_id' => $teacher->id, 'admission_status' => 'admitted']);
        $this->assertDatabaseHas('live_session_attendances', ['live_session_id' => $session->id, 'user_id' => $teacher->id]);
    }

    public function test_provider_sync_updates_operational_state_without_changing_academic_links(): void
    {
        [$teacher, $classroom] = $this->context();
        LiveProviderConnection::query()->create(['user_id' => $teacher->id, 'provider' => 'google_meet', 'access_token' => 'encrypted-token', 'expires_at' => now()->addHour()]);
        $session = $this->liveSession($teacher->id, $classroom->id, ['provider' => 'google_meet', 'provider_meeting_id' => 'calendar-event']);
        Http::fake(['www.googleapis.com/*' => Http::response(['id' => 'calendar-event', 'status' => 'confirmed', 'updated' => now()->toRfc3339String()])]);

        $this->assertTrue(app(LiveProviderSyncService::class)->sync($session));
        $session->refresh();
        $this->assertSame(ProviderSyncStatus::Synced, $session->sync_status);
        $this->assertSame($classroom->id, $session->classroom_id);
        $this->assertNull($session->sync_error);
        $this->assertNotNull($session->last_synced_at);
    }

    public function test_realtime_cleanup_removes_only_expired_transient_data(): void
    {
        [$teacher, $classroom] = $this->context();
        $session = $this->liveSession($teacher->id, $classroom->id);
        $old = LiveSessionSignal::query()->create(['live_session_id' => $session->id, 'sender_id' => $teacher->id, 'recipient_id' => $teacher->id, 'type' => 'offer', 'payload' => []]);
        $old->forceFill(['created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)])->saveQuietly();
        $fresh = LiveSessionSignal::query()->create(['live_session_id' => $session->id, 'sender_id' => $teacher->id, 'recipient_id' => $teacher->id, 'type' => 'offer', 'payload' => []]);
        LiveSessionRoomEvent::query()->create(['live_session_id' => $session->id, 'actor_id' => $teacher->id, 'type' => 'presence', 'payload' => [], 'expires_at' => now()->subMinute()]);

        $this->assertSame(0, Artisan::call('live-sessions:cleanup-realtime'));
        $this->assertModelMissing($old);
        $this->assertModelExists($fresh);
        $this->assertDatabaseCount('live_session_room_events', 0);
    }

    public function test_expired_oauth_token_is_refreshed_once_and_reused(): void
    {
        [$teacher] = $this->context();
        config(['live-providers.google_meet.client_id' => 'client', 'live-providers.google_meet.client_secret' => 'secret']);
        LiveProviderConnection::query()->create(['user_id' => $teacher->id, 'provider' => 'google_meet', 'access_token' => 'expired', 'refresh_token' => 'refresh', 'expires_at' => now()->subMinute()]);
        Http::fake(['oauth2.googleapis.com/token' => Http::response(['access_token' => 'fresh-token', 'expires_in' => 3600])]);

        $tokens = app(LiveProviderTokenService::class);
        $this->assertSame('fresh-token', $tokens->accessToken($teacher->id, LiveSessionProvider::GoogleMeet));
        $this->assertSame('fresh-token', $tokens->accessToken($teacher->id, LiveSessionProvider::GoogleMeet));
        Http::assertSentCount(1);
    }

    public function test_external_redirect_rejects_untrusted_provider_domain(): void
    {
        [$teacher, $classroom] = $this->context();
        $session = $this->liveSession($teacher->id, $classroom->id, ['provider' => 'google_meet', 'provider_join_url' => 'https://meet.google.com.attacker.example/room']);

        $this->actingAs($teacher)->get(route('teacher.live-sessions.room', $session))->assertStatus(502);
    }

    public function test_provider_outage_does_not_prevent_local_session_finalization(): void
    {
        [$teacher, $classroom] = $this->context();
        LiveProviderConnection::query()->create(['user_id' => $teacher->id, 'provider' => 'zoom', 'access_token' => 'zoom-token', 'expires_at' => now()->addHour()]);
        $session = $this->liveSession($teacher->id, $classroom->id, ['provider' => 'zoom', 'provider_meeting_id' => '123456']);
        Http::fake(['api.zoom.us/*' => Http::response(['message' => 'unavailable'], 503)]);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.end', $session))
            ->assertRedirect(route('teacher.live-sessions.index'));

        $session->refresh();
        $this->assertSame('ended', $session->status);
        $this->assertSame(ProviderSyncStatus::Failed, $session->sync_status);
        $this->assertNotNull($session->sync_error);
    }

    public function test_live_session_doctor_is_registered_and_safe_in_local_environment(): void
    {
        $this->assertSame(0, Artisan::call('live-sessions:doctor'));
    }

    private function context(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Phase 13', 'code' => 'P13-'.str()->random(6), 'slug' => 'p13-'.str()->random(8), 'status' => 'active']);

        return [$teacher, $classroom];
    }

    private function liveSession(int $teacherId, int $classroomId, array $overrides = []): LiveSession
    {
        return LiveSession::query()->create([...[
            'classroom_id' => $classroomId, 'teacher_id' => $teacherId, 'created_by' => $teacherId,
            'title' => 'Production room', 'room_name' => 'phase-13-'.str()->random(8), 'provider' => 'native',
            'provider_status' => 'waiting', 'fallback_provider' => 'native', 'sync_status' => 'not_required',
            'session_type' => 'flexible', 'room_settings' => ['waiting_room_enabled' => false, 'recording_enabled' => false],
            'scheduled_start' => now()->subMinute(), 'scheduled_end' => now()->addHour(), 'status' => 'waiting',
        ], ...$overrides]);
    }
}
