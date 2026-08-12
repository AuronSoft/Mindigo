<?php

namespace Tests\Feature;

use App\Jobs\LiveSession\SyncLiveProviderSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Exceptions\ProviderCircuitOpenException;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Notifications\LiveSessionProviderChanged;
use Mindigo\TeacherLiveSession\Services\LiveProviderCircuitBreaker;
use Mindigo\TeacherLiveSession\Services\LiveProviderSyncService;
use Tests\TestCase;

class LiveSessionProviderHealthFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_provider_circuit_opens_after_configured_failures_while_native_stays_available(): void
    {
        config(['live-providers.circuit_breaker.failure_threshold' => 2]);
        $circuit = app(LiveProviderCircuitBreaker::class);
        $circuit->recordFailure(LiveSessionProvider::GoogleMeet);
        $circuit->recordFailure(LiveSessionProvider::GoogleMeet);

        $this->assertFalse($circuit->state(LiveSessionProvider::GoogleMeet)['available']);
        $this->expectException(ProviderCircuitOpenException::class);
        $circuit->assertAvailable(LiveSessionProvider::GoogleMeet);
    }

    public function test_due_external_sessions_are_dispatched_to_queue(): void
    {
        Bus::fake();
        [$teacher, $classroom] = $this->context();
        $session = $this->liveSession($teacher->id, $classroom->id);

        $stats = app(LiveProviderSyncService::class)->syncDue();

        $this->assertSame(['queued' => 1], $stats);
        Bus::assertDispatched(SyncLiveProviderSession::class, fn ($job) => $job->liveSessionId === $session->id);
    }

    public function test_teacher_can_fallback_upcoming_external_room_without_changing_academic_context(): void
    {
        Notification::fake();
        [$teacher, $classroom] = $this->context();
        $student = $this->createUser(['role' => 'student']);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $session = $this->liveSession($teacher->id, $classroom->id);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.fallback-native', $session))->assertRedirect();

        $session->refresh();
        $this->assertSame(LiveSessionProvider::Native, $session->provider);
        $this->assertSame($classroom->id, $session->classroom_id);
        $this->assertNull($session->classroom_schedule_id);
        $this->assertSame('scheduled', $session->status);
        Notification::assertSentTo($student, LiveSessionProviderChanged::class);
    }

    public function test_live_external_room_cannot_be_hot_transferred(): void
    {
        [$teacher, $classroom] = $this->context();
        $session = $this->liveSession($teacher->id, $classroom->id, ['status' => 'live', 'started_at' => now()]);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.fallback-native', $session))->assertStatus(422);
        $this->assertSame(LiveSessionProvider::GoogleMeet, $session->fresh()->provider);
    }

    public function test_provider_health_page_is_admin_only(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($teacher)->get(route('admin.live-providers.health'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.live-providers.health'))->assertOk();
    }

    private function context(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = Classroom::query()->create(['created_by' => $teacher->id, 'teacher_id' => $teacher->id, 'type' => Classroom::TYPE_STANDALONE, 'name' => 'Phase 14', 'code' => 'P14-'.str()->random(6), 'slug' => 'p14-'.str()->random(8), 'status' => 'active']);

        return [$teacher, $classroom];
    }

    private function liveSession(int $teacherId, int $classroomId, array $overrides = []): LiveSession
    {
        return LiveSession::query()->create([...['classroom_id' => $classroomId, 'teacher_id' => $teacherId, 'created_by' => $teacherId,
            'title' => 'External room', 'room_name' => 'external-room', 'provider' => 'google_meet', 'provider_meeting_id' => 'event-14',
            'provider_join_url' => 'https://meet.google.com/abc-defg-hij', 'provider_host_url' => 'https://meet.google.com/abc-defg-hij',
            'provider_status' => 'confirmed', 'fallback_provider' => 'native', 'sync_status' => 'pending', 'session_type' => 'flexible',
            'room_settings' => [], 'scheduled_start' => now()->addHour(), 'scheduled_end' => now()->addHours(2), 'status' => 'scheduled',
        ], ...$overrides]);
    }
}
