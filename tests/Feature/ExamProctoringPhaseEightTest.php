<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamProctorEvent;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Tests\TestCase;

class ExamProctoringPhaseEightTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_eight_schema_stores_events_separately_and_only_exposes_risk(): void
    {
        $this->assertTrue(Schema::hasTable('exam_proctor_events'));
        $this->assertTrue(Schema::hasColumns('exam_session_attempts', [
            'proctor_session_key', 'initial_ip_hash', 'initial_device_hash', 'risk_score',
            'risk_level', 'camera_consent_at', 'terminated_by', 'terminated_at',
        ]));
        $this->assertFalse(Schema::hasColumn('exam_session_attempts', 'is_cheating'));
    }

    public function test_client_event_is_persisted_immediately_and_updates_risk_level(): void
    {
        [, $student, , $attempt] = $this->fixture();

        $this->actingAs($student)->postJson(route('student.exam-sessions.security-event', $attempt), [
            'type' => ExamProctorEvent::TYPE_FULLSCREEN_EXITED,
            'session_key' => 'browser-a',
            'device_key' => 'device-a',
            'metadata' => ['fullscreen' => false],
        ])->assertOk()->assertJson(['ok' => true, 'risk_level' => ExamProctorEvent::RISK_LOW]);

        $this->assertDatabaseHas('exam_proctor_events', [
            'exam_session_attempt_id' => $attempt->id,
            'type' => ExamProctorEvent::TYPE_FULLSCREEN_EXITED,
            'risk_weight' => 10,
        ]);
        $this->assertSame(10, $attempt->fresh()->risk_score);
    }

    public function test_server_detects_concurrent_session_ip_and_device_changes(): void
    {
        [, $student, , $attempt] = $this->fixture();

        $this->actingAs($student)->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->postJson(route('student.exam-sessions.heartbeat', $attempt), ['session_key' => 'browser-a', 'device_key' => 'device-a'])
            ->assertOk();
        $this->actingAs($student)->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->postJson(route('student.exam-sessions.heartbeat', $attempt), ['session_key' => 'browser-b', 'device_key' => 'device-b'])
            ->assertOk();

        $this->assertDatabaseHas('exam_proctor_events', ['exam_session_attempt_id' => $attempt->id, 'type' => ExamProctorEvent::TYPE_CONCURRENT_SESSION]);
        $this->assertDatabaseHas('exam_proctor_events', ['exam_session_attempt_id' => $attempt->id, 'type' => ExamProctorEvent::TYPE_IP_CHANGED]);
        $this->assertDatabaseHas('exam_proctor_events', ['exam_session_attempt_id' => $attempt->id, 'type' => ExamProctorEvent::TYPE_DEVICE_CHANGED]);
        $this->assertSame(ExamProctorEvent::RISK_HIGH, $attempt->fresh()->risk_level);
    }

    public function test_camera_requires_explicit_consent_and_records_the_decision(): void
    {
        [, $student, , $attempt] = $this->fixture();

        $this->actingAs($student)->postJson(route('student.exam-sessions.camera-consent', $attempt), [
            'consented' => true, 'session_key' => 'browser-a', 'device_key' => 'device-a',
        ])->assertOk();

        $this->assertNotNull($attempt->fresh()->camera_consent_at);
        $this->assertDatabaseHas('exam_proctor_events', [
            'exam_session_attempt_id' => $attempt->id,
            'type' => ExamProctorEvent::TYPE_CAMERA_CONSENT_GRANTED,
            'risk_weight' => 0,
        ]);
    }

    public function test_only_the_organizer_can_note_and_terminate_an_attempt(): void
    {
        [$teacher, , $session, $attempt] = $this->fixture();
        $otherTeacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($otherTeacher)->post(route('teacher.exam-sessions.proctor.note', [$session, $attempt]), ['note' => 'Review this'])->assertForbidden();
        $this->actingAs($teacher)->post(route('teacher.exam-sessions.proctor.note', [$session, $attempt]), ['note' => 'Connection issue'])->assertRedirect();
        $this->actingAs($teacher)->post(route('teacher.exam-sessions.proctor.terminate', [$session, $attempt]), ['reason' => 'Student requested assistance'])->assertRedirect();

        $attempt->refresh();
        $this->assertSame(ExamSessionAttempt::STATUS_TERMINATED, $attempt->status);
        $this->assertSame($teacher->id, $attempt->terminated_by);
        $this->assertDatabaseHas('exam_proctor_events', ['exam_session_attempt_id' => $attempt->id, 'type' => ExamProctorEvent::TYPE_PROCTOR_NOTE]);
        $this->assertDatabaseHas('exam_proctor_events', ['exam_session_attempt_id' => $attempt->id, 'type' => ExamProctorEvent::TYPE_ATTEMPT_TERMINATED]);
    }

    private function fixture(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $template = ExamTemplate::query()->create(['owner_id' => $teacher->id, 'title' => 'Proctoring template', 'slug' => 'proctoring-'.str()->lower(str()->random(6)), 'status' => 'ready', 'ready_at' => now()]);
        $version = ExamTemplateVersion::query()->create(['exam_template_id' => $template->id, 'created_by' => $teacher->id, 'version' => 1, 'title' => $template->title, 'total_questions' => 1, 'total_points' => 1, 'locked_at' => now()]);
        ExamTemplateQuestion::query()->create(['exam_template_version_id' => $version->id, 'sort_order' => 1, 'type' => 'single_choice', 'content' => 'Question', 'options' => [['key' => 'A', 'text' => 'Answer']], 'correct_answers' => ['A'], 'points' => 1]);
        $session = ExamSession::query()->create([
            'exam_template_version_id' => $version->id, 'organizer_id' => $teacher->id,
            'title' => 'Proctored exam', 'slug' => 'proctored-'.str()->lower(str()->random(6)),
            'status' => ExamSession::STATUS_SCHEDULED, 'starts_at' => now()->subMinute(), 'ends_at' => now()->addHour(),
            'duration_minutes' => 45, 'max_attempts' => 1, 'passing_score' => 1,
            'security_policy' => [
                'fullscreen' => true, 'tab_switch_detection' => true, 'clipboard_detection' => true,
                'multiple_sessions_detection' => true, 'ip_change_detection' => true,
                'device_change_detection' => true, 'heartbeat_detection' => true,
                'refresh_detection' => true, 'camera_enabled' => true,
            ],
        ]);
        $candidate = ExamCandidate::query()->create(['exam_session_id' => $session->id, 'user_id' => $student->id, 'name' => $student->name, 'email' => $student->email, 'status' => ExamCandidate::STATUS_ELIGIBLE]);
        $this->actingAs($student)->post(route('student.exam-sessions.start', $session));

        return [$teacher, $student, $session, ExamSessionAttempt::query()->where('exam_candidate_id', $candidate->id)->firstOrFail()];
    }
}
