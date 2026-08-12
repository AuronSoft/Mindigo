<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Data\ProviderCapabilities;
use Mindigo\TeacherLiveSession\Data\ProviderMeeting;
use Mindigo\TeacherLiveSession\Data\SessionContext;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Models\LiveProviderConnection;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;
use Mindigo\TeacherLiveSession\Services\LiveSessionOperationalMonitor;
use Mindigo\TeacherLiveSession\Services\LiveSessionService;
use Tests\TestCase;

class LiveSessionPhaseEighteenHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_providers_obey_the_same_creation_contract(): void
    {
        [$teacher, $classroom] = $this->context();
        $this->connections($teacher->id);
        Http::fake([
            'www.googleapis.com/*' => Http::response(['id' => 'google-event', 'hangoutLink' => 'https://meet.google.com/abc-defg-hij', 'status' => 'confirmed']),
            'api.zoom.us/*' => Http::response(['id' => 123456, 'uuid' => 'zoom-uuid', 'join_url' => 'https://zoom.us/j/123456', 'start_url' => 'https://zoom.us/s/123456?zak=short-lived', 'password' => 'must-not-persist', 'status' => 'waiting']),
        ]);
        $context = new SessionContext($classroom->id, $teacher->id, 'Contract room', null, now()->addDay(), now()->addDay()->addHour(), (string) str()->uuid());
        $registry = app(LiveMeetingProviderRegistry::class);

        foreach ([LiveSessionProvider::Native, LiveSessionProvider::GoogleMeet, LiveSessionProvider::Zoom] as $provider) {
            $adapter = $registry->resolve($provider);
            $this->assertInstanceOf(ProviderMeeting::class, $meeting = $adapter->create($context));
            $this->assertInstanceOf(ProviderCapabilities::class, $adapter->capabilities());
            $this->assertNotSame('', $meeting->roomName);
            $this->assertArrayNotHasKey('password', $meeting->metadata);
        }
    }

    public function test_replayed_create_request_returns_one_session_and_calls_provider_once(): void
    {
        [$teacher, $classroom] = $this->context();
        $this->connections($teacher->id);
        Http::fake(['www.googleapis.com/*' => Http::response(['id' => 'event-once', 'hangoutLink' => 'https://meet.google.com/abc-defg-hij', 'status' => 'confirmed'])]);
        $payload = $this->payload($classroom, ['provider' => 'google_meet', 'idempotency_key' => (string) str()->uuid()]);
        $service = app(LiveSessionService::class);

        $first = $service->create($payload, $teacher);
        $second = $service->create($payload, $teacher);

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('live_sessions', 1);
        Http::assertSentCount(1);
    }

    public function test_untrusted_role_cannot_access_teacher_room_or_admin_monitoring(): void
    {
        [$teacher, $classroom] = $this->context();
        $student = $this->createUser(['role' => 'student']);
        $session = LiveSession::query()->create([...$this->payload($classroom),
            'teacher_id' => $teacher->id, 'created_by' => $teacher->id, 'room_name' => 'private-room',
            'fallback_provider' => 'native', 'provider_status' => 'ready', 'sync_status' => 'not_required', 'status' => 'scheduled',
        ]);

        $this->actingAs($student)->get(route('teacher.live-sessions.room', $session))->assertRedirect();
        $this->actingAs($teacher)->get(route('admin.live-providers.health'))->assertForbidden();
    }

    public function test_operational_monitor_reports_stale_rooms_and_recent_failures(): void
    {
        [$teacher, $classroom] = $this->context();
        LiveSession::query()->create([...$this->payload($classroom),
            'teacher_id' => $teacher->id, 'created_by' => $teacher->id, 'room_name' => 'stale-room',
            'fallback_provider' => 'native', 'provider_status' => 'ready', 'sync_status' => 'failed',
            'status' => 'live', 'scheduled_start' => now()->subHours(3), 'scheduled_end' => now()->subHours(2),
        ]);

        $codes = collect(app(LiveSessionOperationalMonitor::class)->alerts())->pluck('code');

        $this->assertContains('provider_sync_failures', $codes);
        $this->assertContains('stale_live_rooms', $codes);
    }

    private function context(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Phase 18', 'code' => 'P18-'.str()->random(6), 'slug' => 'p18-'.str()->random(8), 'status' => 'active',
        ]);

        return [$teacher, $classroom];
    }

    private function connections(int $teacherId): void
    {
        foreach (['google_meet', 'zoom'] as $provider) {
            LiveProviderConnection::query()->create(['user_id' => $teacherId, 'provider' => $provider, 'access_token' => $provider.'-token', 'expires_at' => now()->addHour()]);
        }
    }

    private function payload(Classroom $classroom, array $overrides = []): array
    {
        return [...[
            'classroom_id' => $classroom->id, 'title' => 'Hardened room', 'provider' => 'native',
            'session_type' => 'flexible', 'scheduled_start' => now()->addDay(), 'scheduled_end' => now()->addDay()->addHour(),
            'room_settings' => ['waiting_room_enabled' => true, 'recording_enabled' => false],
        ], ...$overrides];
    }
}
