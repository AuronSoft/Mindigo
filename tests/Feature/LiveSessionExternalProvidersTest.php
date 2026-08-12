<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherLiveSession\Data\SessionContext;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Models\LiveProviderConnection;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Providers\Meetings\GoogleMeetProvider;
use Tests\TestCase;

class LiveSessionExternalProvidersTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_meet_adapter_creates_calendar_conference_without_exposing_token(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        LiveProviderConnection::query()->create(['user_id' => $teacher->id, 'provider' => 'google_meet', 'access_token' => 'secret-token', 'expires_at' => now()->addHour()]);
        Http::fake(['www.googleapis.com/*' => Http::response(['id' => 'event-1', 'status' => 'confirmed', 'hangoutLink' => 'https://meet.google.com/abc-defg-hij'])]);

        $meeting = app(GoogleMeetProvider::class)->create(new SessionContext(1, $teacher->id, 'Math', null, now()->addDay(), now()->addDay()->addHour(), 'request-1'));

        $this->assertSame('https://meet.google.com/abc-defg-hij', $meeting->joinUrl);
        $this->assertArrayNotHasKey('access_token', LiveProviderConnection::query()->first()->toArray());
        Http::assertSent(fn ($request) => str_contains($request->url(), 'conferenceDataVersion=1') && $request['conferenceData']['createRequest']['requestId'] === 'request-1');
    }

    public function test_external_api_failure_falls_back_to_independent_native_room(): void
    {
        config(['live-providers.google_meet.client_id' => 'id', 'live-providers.google_meet.client_secret' => 'secret', 'live-providers.google_meet.redirect_uri' => 'https://app.test/callback']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Room', 'code' => 'ROOM-P12', 'slug' => 'room-p12', 'status' => 'active']);
        LiveProviderConnection::query()->create(['user_id' => $teacher->id, 'provider' => 'google_meet', 'access_token' => 'secret-token', 'expires_at' => now()->addHour()]);
        Http::fake(['www.googleapis.com/*' => Http::response(['error' => 'down'], 503)]);

        $response = $this->actingAs($teacher)->post(route('teacher.live-sessions.store'), ['title' => 'Fallback', 'classroom_id' => $classroom->id, 'provider' => 'google_meet', 'scheduled_start' => now()->addDay(), 'scheduled_end' => now()->addDay()->addHour()]);

        $response->assertRedirect(route('teacher.live-sessions.index'));
        $session = LiveSession::query()->sole();
        $this->assertSame(LiveSessionProvider::Native, $session->provider);
        $this->assertSame('google_meet', $session->provider_metadata['fallback_from']);
    }

    public function test_external_room_keeps_classroom_and_schedule_as_the_system_source_of_truth(): void
    {
        config(['live-providers.google_meet.client_id' => 'id', 'live-providers.google_meet.client_secret' => 'secret', 'live-providers.google_meet.redirect_uri' => 'https://app.test/callback']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Linked room', 'code' => 'LINK-P12', 'slug' => 'link-p12', 'status' => 'active']);
        $schedule = ClassroomSchedule::query()->create(['classroom_id' => $classroom->id, 'type' => ClassroomSchedule::TYPE_REGULAR, 'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE, 'status' => ClassroomSchedule::STATUS_SCHEDULED, 'title' => 'Linked schedule', 'session_date' => now()->addDays(2)->toDateString(), 'start_time' => '08:00', 'end_time' => '09:00']);
        LiveProviderConnection::query()->create(['user_id' => $teacher->id, 'provider' => 'google_meet', 'access_token' => 'secret-token', 'expires_at' => now()->addHour()]);
        Http::fake(['www.googleapis.com/*' => Http::response(['id' => 'linked-event', 'status' => 'confirmed', 'hangoutLink' => 'https://meet.google.com/linked-room'])]);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.store'), [
            'title' => 'Linked provider room', 'classroom_id' => $classroom->id, 'classroom_schedule_id' => $schedule->id,
            'provider' => 'google_meet', 'scheduled_start' => now()->addDays(2)->setTime(8, 0), 'scheduled_end' => now()->addDays(2)->setTime(9, 0),
        ])->assertRedirect(route('teacher.live-sessions.index'));

        $session = LiveSession::query()->sole();
        $this->assertSame($classroom->id, $session->classroom_id);
        $this->assertSame($schedule->id, $session->classroom_schedule_id);
        $this->assertSame(ClassroomSchedule::DELIVERY_ONLINE, $schedule->fresh()->delivery_mode);
        $this->assertSame('linked-event', $session->provider_meeting_id);
    }
}
