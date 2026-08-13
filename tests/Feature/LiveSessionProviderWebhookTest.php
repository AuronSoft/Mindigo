<?php

namespace Tests\Feature;

use App\Jobs\LiveSession\ProcessLiveProviderWebhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Models\LiveProviderConnection;
use Mindigo\TeacherLiveSession\Models\LiveProviderParticipant;
use Mindigo\TeacherLiveSession\Models\LiveProviderWebhookEvent;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;
use Mindigo\TeacherLiveSession\Services\LiveProviderSubscriptionService;
use Mindigo\TeacherLiveSession\Services\LiveProviderWebhookProcessor;
use Tests\TestCase;

final class LiveSessionProviderWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_zoom_rejects_unsigned_and_replayed_webhooks(): void
    {
        config(['live-providers.zoom.webhook_secret' => 'webhook-secret', 'live-providers.zoom.webhook_tolerance_seconds' => 300]);
        $payload = ['event' => 'meeting.started', 'event_ts' => 123, 'payload' => ['object' => ['id' => '9001']]];

        $this->postJson(route('webhooks.live-providers.zoom'), $payload)->assertForbidden();

        Queue::fake();
        $this->signedZoom($payload)->assertNoContent();
        $this->signedZoom($payload)->assertNoContent();

        $this->assertDatabaseCount('live_provider_webhook_events', 1);
        Queue::assertPushed(ProcessLiveProviderWebhook::class, 1);
    }

    public function test_zoom_lifecycle_participant_and_recording_events_update_the_session(): void
    {
        $session = $this->zoomSession();
        $events = [
            ['meeting.started', ['object' => ['id' => '9001']]],
            ['meeting.participant_joined', ['object' => ['id' => '9001', 'participant' => ['id' => 'person-1', 'participant_user_id' => 'connection-1', 'user_name' => 'Student', 'join_time' => now()->toIso8601String()]]]],
            ['recording.completed', ['object' => ['id' => '9001', 'recording_files' => [['id' => 'recording-1', 'file_type' => 'MP4', 'play_url' => 'https://zoom.test/recording']]]]],
            ['meeting.ended', ['object' => ['id' => '9001']]],
        ];

        foreach ($events as $index => [$type, $payload]) {
            $event = LiveProviderWebhookEvent::query()->create(['provider' => 'zoom', 'event_id' => 'event-'.$index, 'event_type' => $type, 'payload' => ['payload' => $payload], 'status' => 'pending', 'received_at' => now()]);
            app(LiveProviderWebhookProcessor::class)->process($event);
        }

        $this->assertSame('ended', $session->fresh()->status);
        $this->assertDatabaseHas(LiveProviderParticipant::class, ['live_session_id' => $session->id, 'provider_participant_id' => 'person-1']);
        $this->assertDatabaseHas(LiveSessionRecording::class, ['live_session_id' => $session->id, 'provider_recording_id' => 'recording-1', 'status' => 'ready']);
    }

    public function test_google_calendar_requires_the_registered_channel_token(): void
    {
        config(['live-providers.google_meet.webhook_token' => 'google-channel-secret']);
        Queue::fake();

        $this->post(route('webhooks.live-providers.google-calendar'))->assertForbidden();
        $this->withHeaders(['X-Goog-Channel-Token' => 'google-channel-secret', 'X-Goog-Channel-Id' => 'channel-1', 'X-Goog-Message-Number' => '2', 'X-Goog-Resource-State' => 'exists'])
            ->post(route('webhooks.live-providers.google-calendar'))->assertNoContent();

        $this->assertDatabaseHas('live_provider_webhook_events', ['provider' => 'google_meet', 'event_id' => 'channel-1:2', 'event_type' => 'calendar.changed']);
    }

    public function test_google_calendar_watch_is_registered_and_persisted_for_renewal(): void
    {
        config([
            'live-providers.google_meet.webhook_token' => 'google-channel-secret',
            'live-providers.google_meet.calendar_webhook_url' => 'https://lms.test/webhooks/live-providers/google-calendar',
        ]);
        $teacher = $this->createUser(['role' => 'teacher']);
        $connection = LiveProviderConnection::query()->create(['user_id' => $teacher->id, 'provider' => 'google_meet', 'access_token' => 'access-token', 'expires_at' => now()->addHour()]);
        Http::fake(['www.googleapis.com/*' => Http::response(['resourceId' => 'resource-1', 'resourceUri' => 'https://www.googleapis.com/calendar/v3/calendars/primary/events', 'expiration' => (string) (now()->addDays(7)->timestamp * 1000)])]);

        $subscription = app(LiveProviderSubscriptionService::class)->watchGoogleCalendar($connection);

        $this->assertSame('resource-1', $subscription->resource_id);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/events/watch')
            && $request['type'] === 'web_hook'
            && $request['token'] === 'google-channel-secret');
    }

    private function signedZoom(array $payload)
    {
        $timestamp = now()->timestamp;
        $content = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = 'v0='.hash_hmac('sha256', 'v0:'.$timestamp.':'.$content, 'webhook-secret');

        return $this->call('POST', route('webhooks.live-providers.zoom'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_ZM_REQUEST_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_ZM_SIGNATURE' => $signature,
        ], $content);
    }

    private function zoomSession(): LiveSession
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Webhook room', 'code' => 'WEBHOOK-22', 'slug' => 'webhook-22', 'status' => 'active']);

        return LiveSession::query()->create(['classroom_id' => $classroom->id, 'teacher_id' => $teacher->id, 'created_by' => $teacher->id, 'title' => 'Zoom webhook', 'room_name' => 'zoom-webhook', 'provider' => 'zoom', 'provider_meeting_id' => '9001', 'scheduled_start' => now(), 'scheduled_end' => now()->addHour(), 'status' => 'scheduled']);
    }
}
