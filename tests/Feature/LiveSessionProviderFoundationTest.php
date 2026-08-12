<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Providers\Meetings\MindigoNativeProvider;
use Mindigo\TeacherLiveSession\Providers\Meetings\ZoomProvider;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;
use Tests\TestCase;

class LiveSessionProviderFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_native_provider_is_registered_as_the_independent_default(): void
    {
        $registry = $this->app->make(LiveMeetingProviderRegistry::class);

        $this->assertInstanceOf(MindigoNativeProvider::class, $registry->resolve(LiveSessionProvider::Native));
        $this->assertTrue($registry->resolve('native')->health()->available);
        $this->assertTrue($registry->resolve('native')->capabilities()->embedded);
        $this->assertInstanceOf(ZoomProvider::class, $registry->resolve(LiveSessionProvider::Zoom));
        $this->assertFalse($registry->resolve(LiveSessionProvider::Zoom)->capabilities()->embedded);
    }

    public function test_teacher_creates_native_session_with_provider_identity(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroomFor($teacher->id, 'OWN-CLASS');

        $response = $this->actingAs($teacher)->post(route('teacher.live-sessions.store'), [
            'title' => 'Native lesson',
            'classroom_id' => $classroom->id,
            'scheduled_start' => now()->addDay()->format('Y-m-d H:i:s'),
            'scheduled_end' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('teacher.live-sessions.index'));
        $session = LiveSession::query()->sole();
        $this->assertSame(LiveSessionProvider::Native, $session->provider);
        $this->assertSame($teacher->id, $session->created_by);
        $this->assertNotNull($session->idempotency_key);
        $this->assertStringStartsWith('mindigo-', $session->room_name);
    }

    public function test_teacher_cannot_create_a_session_for_another_teachers_classroom(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $other = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroomFor($other->id, 'OTHER-CLASS');

        $response = $this->actingAs($teacher)->from(route('teacher.live-sessions.create'))->post(route('teacher.live-sessions.store'), [
            'title' => 'Unauthorized lesson',
            'classroom_id' => $classroom->id,
            'scheduled_start' => now()->addDay()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('teacher.live-sessions.create'));
        $response->assertSessionHasErrors('classroom_id');
        $this->assertDatabaseCount('live_sessions', 0);
    }

    public function test_unregistered_external_provider_is_rejected_at_the_request_boundary(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroomFor($teacher->id, 'NATIVE-ONLY');

        $response = $this->actingAs($teacher)->post(route('teacher.live-sessions.store'), [
            'title' => 'Unavailable provider',
            'classroom_id' => $classroom->id,
            'provider' => LiveSessionProvider::Zoom->value,
            'scheduled_start' => now()->addDay()->format('Y-m-d H:i:s'),
            'scheduled_end' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('provider');
        $this->assertDatabaseCount('live_sessions', 0);
    }

    private function classroomFor(int $teacherId, string $code): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId,
            'teacher_id' => $teacherId,
            'type' => Classroom::TYPE_STANDALONE,
            'name' => $code,
            'code' => $code,
            'slug' => strtolower($code),
            'status' => 'active',
        ]);
    }
}
