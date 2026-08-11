<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mindigo\AuditLog\Models\AuditLog;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\LiveSessionStatus;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;
use Mindigo\TeacherLiveSession\Services\LiveSessionJoinTokenService;
use Tests\TestCase;

class LiveSessionSecurityLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_controls_the_strict_lifecycle_and_actions_are_audited(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $session = $this->sessionFor($teacher->id);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.open', $session))->assertRedirect();
        $this->assertSame(LiveSessionStatus::Waiting->value, $session->fresh()->status);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.start', $session))->assertRedirect(route('teacher.live-sessions.room', $session));
        $this->assertSame(LiveSessionStatus::Live->value, $session->fresh()->status);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.end', $session))->assertRedirect(route('teacher.live-sessions.index'));
        $session->refresh();
        $this->assertSame(LiveSessionStatus::Ended->value, $session->status);
        $this->assertNotNull($session->ended_at);
        $this->assertNotNull($session->locked_at);
        $this->assertGreaterThan(1, $session->join_token_version);
        $this->assertGreaterThanOrEqual(3, AuditLog::query()->where('module', 'teacher_live_session')->count());

        $this->actingAs($teacher)->post(route('teacher.live-sessions.start', $session))->assertSessionHasErrors('status');
    }

    public function test_student_waits_for_admission_then_receives_a_revocable_short_lived_token(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $session = $this->sessionFor($teacher->id, ['scheduled_start' => now()->addMinutes(10), 'scheduled_end' => now()->addHour()]);
        $session->classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.open', $session))->assertRedirect();
        $this->actingAs($student)->get(route('student.live-sessions.room', $session))->assertOk()->assertViewIs('student-live-session::waiting');

        $participant = LiveSessionParticipant::query()->where('user_id', $student->id)->sole();
        $this->assertSame(ParticipantAdmissionStatus::Waiting, $participant->admission_status);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.participants.admit', [$session, $participant]))->assertRedirect();
        $this->actingAs($teacher)->post(route('teacher.live-sessions.start', $session))->assertRedirect();

        $response = $this->actingAs($student)->postJson(route('student.live-sessions.join-token', $session));
        $response->assertOk()->assertJsonStructure(['token', 'expires_in']);
        $token = $response->json('token');
        $claims = app(LiveSessionJoinTokenService::class)->validate($token, $session->fresh(), $student);
        $this->assertSame(LiveParticipantRole::Student->value, $claims['role']);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.lock', $session))->assertRedirect();
        $this->expectException(ValidationException::class);
        app(LiveSessionJoinTokenService::class)->validate($token, $session->fresh(), $student);
    }

    public function test_assistant_can_moderate_but_cannot_manage_the_session_definition(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $assistant = $this->createUser(['role' => 'teacher']);
        $session = $this->sessionFor($teacher->id, classroomAttributes: ['assistant_id' => $assistant->id]);

        $this->actingAs($assistant)->post(route('teacher.live-sessions.open', $session))->assertRedirect();
        $this->assertSame(LiveSessionStatus::Waiting->value, $session->fresh()->status);
        $this->actingAs($assistant)->get(route('teacher.live-sessions.index'))->assertOk()->assertSee($session->title);
        $this->actingAs($assistant)->get(route('teacher.live-sessions.edit', $session))->assertForbidden();
        $this->actingAs($assistant)->post(route('teacher.live-sessions.cancel-session', $session), ['reason' => 'Assistant must not cancel this class'])->assertForbidden();
    }

    public function test_outsider_cannot_enter_or_control_a_live_session(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $outsider = $this->createUser(['role' => 'student']);
        $session = $this->sessionFor($teacher->id, ['status' => LiveSessionStatus::Live->value]);

        $this->actingAs($outsider)->get(route('student.live-sessions.room', $session))->assertForbidden();
        $this->actingAs($outsider)->postJson(route('student.live-sessions.join-token', $session))->assertForbidden();
    }

    public function test_room_cannot_open_outside_host_window_and_owner_can_cancel_with_reason(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $future = $this->sessionFor($teacher->id, ['scheduled_start' => now()->addHours(3), 'scheduled_end' => now()->addHours(4)]);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.open', $future))->assertForbidden();
        $this->actingAs($teacher)->post(route('teacher.live-sessions.cancel-session', $future), [
            'reason' => 'The class must be cancelled due to a schedule change.',
        ])->assertRedirect(route('teacher.live-sessions.index'));

        $future->refresh();
        $this->assertSame(LiveSessionStatus::Cancelled->value, $future->status);
        $this->assertNotNull($future->cancelled_at);
        $this->assertSame($teacher->id, $future->cancelled_by);
        $this->assertNotNull($future->cancel_reason);
    }

    private function sessionFor(int $teacherId, array $attributes = [], array $classroomAttributes = []): LiveSession
    {
        $classroom = Classroom::query()->create([
            'created_by' => $teacherId,
            'teacher_id' => $teacherId,
            'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Secure classroom',
            'code' => 'SEC-'.uniqid(),
            'slug' => 'secure-'.uniqid(),
            'status' => 'active',
            ...$classroomAttributes,
        ]);

        return LiveSession::query()->create([
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacherId,
            'created_by' => $teacherId,
            'title' => 'Secure live lesson',
            'room_name' => 'mindigo-'.uniqid(),
            'provider' => 'native',
            'provider_status' => 'ready',
            'fallback_provider' => 'native',
            'sync_status' => 'not_required',
            'session_type' => 'flexible',
            'room_settings' => ['waiting_room_enabled' => true],
            'scheduled_start' => now()->addMinutes(30),
            'scheduled_end' => now()->addHours(2),
            'status' => LiveSessionStatus::Scheduled->value,
            ...$attributes,
        ]);
    }
}
