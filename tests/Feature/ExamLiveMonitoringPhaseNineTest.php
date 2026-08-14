<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Mindigo\ExamManagement\Events\ExamMonitoringUpdated;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamProctorEvent;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Tests\TestCase;

class ExamLiveMonitoringPhaseNineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([ExamMonitoringUpdated::class]);
    }

    public function test_phase_nine_schema_supports_pause_time_and_warning_state(): void
    {
        $this->assertTrue(Schema::hasColumns('exam_session_attempts', [
            'paused_at', 'paused_by', 'pause_remaining_seconds', 'added_time_minutes',
            'latest_warning', 'latest_warning_at',
        ]));
    }

    public function test_only_the_exam_organizer_can_open_live_monitoring(): void
    {
        [$teacher, , $session] = $this->fixture(false);
        $otherTeacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('teacher.exam-sessions.monitoring.index', $session))
            ->assertOk()->assertSee(__('Mindigo-exam-management::app.monitoring.title'));
        $this->actingAs($teacher)->getJson(route('teacher.exam-sessions.monitoring.data', $session))
            ->assertOk()->assertJsonPath('summary.not_started', 1);
        $this->actingAs($teacher)->getJson(route('teacher.exam-sessions.monitoring.data', ['session' => $session, 'status' => 'invalid']))->assertUnprocessable();
        $this->actingAs($otherTeacher)->get(route('teacher.exam-sessions.monitoring.index', $session))->assertForbidden();
    }

    public function test_dashboard_reports_activity_question_warning_and_integrity(): void
    {
        [$teacher, $student, $session, $attempt, $question] = $this->fixture();
        $this->actingAs($student)->postJson(route('student.exam-sessions.heartbeat', $attempt), [
            'session_key' => 'browser-a', 'device_key' => 'device-a', 'current_question_id' => $question->id,
        ])->assertOk();
        $this->actingAs($student)->postJson(route('student.exam-sessions.security-event', $attempt), [
            'type' => ExamProctorEvent::TYPE_FULLSCREEN_EXITED, 'session_key' => 'browser-a', 'device_key' => 'device-a',
        ])->assertOk();

        $response = $this->actingAs($teacher)->getJson(route('teacher.exam-sessions.monitoring.data', $session))
            ->assertOk()->assertJsonPath('summary.in_progress', 1);
        $this->assertStringContainsString('90/100', $response->json('html'));
        $this->assertStringContainsString('>1<', $response->json('html'));
        ExamMonitoringUpdated::dispatch($session->id, $attempt->id, 'heartbeat');
        Event::assertDispatched(ExamMonitoringUpdated::class, fn (ExamMonitoringUpdated $event): bool => $event->sessionId === $session->id);
    }

    public function test_teacher_can_add_time_warn_pause_resume_and_terminate(): void
    {
        [$teacher, , $session, $attempt] = $this->fixture();
        $originalExpiry = $attempt->expires_at;

        $this->actingAs($teacher)->post(route('teacher.exam-sessions.monitoring.add-time', [$session, $attempt]), ['minutes' => 10])->assertRedirect();
        $this->assertTrue($attempt->fresh()->expires_at->equalTo($originalExpiry->addMinutes(10)));
        $this->actingAs($teacher)->post(route('teacher.exam-sessions.monitoring.warning', [$session, $attempt]), ['message' => 'Please remain in the exam tab.'])->assertRedirect();
        $this->assertSame('Please remain in the exam tab.', $attempt->fresh()->latest_warning);
        $this->actingAs($teacher)->post(route('teacher.exam-sessions.monitoring.pause', [$session, $attempt]))->assertRedirect();
        $this->assertSame(ExamSessionAttempt::STATUS_PAUSED, $attempt->fresh()->status);
        $this->actingAs($teacher)->post(route('teacher.exam-sessions.monitoring.resume', [$session, $attempt]))->assertRedirect();
        $this->assertSame(ExamSessionAttempt::STATUS_IN_PROGRESS, $attempt->fresh()->status);
        $this->actingAs($teacher)->post(route('teacher.exam-sessions.proctor.terminate', [$session, $attempt]), ['reason' => 'Candidate requested termination.'])->assertRedirect();
        $this->assertSame(ExamSessionAttempt::STATUS_TERMINATED, $attempt->fresh()->status);
    }

    public function test_teacher_can_allow_exactly_an_additional_attempt(): void
    {
        [$teacher, , $session, $attempt] = $this->fixture();
        $attempt->update(['status' => ExamSessionAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);

        $this->actingAs($teacher)->post(route('teacher.exam-sessions.monitoring.retry', [$session, $attempt]))->assertRedirect();

        $this->assertSame(2, $attempt->candidate->fresh()->max_attempts_override);
        $this->assertDatabaseHas('exam_proctor_events', ['exam_session_attempt_id' => $attempt->id, 'type' => ExamProctorEvent::TYPE_RETRY_ALLOWED]);
    }

    private function fixture(bool $start = true): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $template = ExamTemplate::query()->create(['owner_id' => $teacher->id, 'title' => 'Live monitor template', 'slug' => 'live-monitor-'.str()->lower(str()->random(6)), 'status' => 'ready', 'ready_at' => now()]);
        $version = ExamTemplateVersion::query()->create(['exam_template_id' => $template->id, 'created_by' => $teacher->id, 'version' => 1, 'title' => $template->title, 'total_questions' => 1, 'total_points' => 1, 'locked_at' => now()]);
        $question = ExamTemplateQuestion::query()->create(['exam_template_version_id' => $version->id, 'sort_order' => 1, 'type' => 'single_choice', 'content' => 'Question', 'options' => [['key' => 'A', 'text' => 'Answer']], 'correct_answers' => ['A'], 'points' => 1]);
        $session = ExamSession::query()->create([
            'exam_template_version_id' => $version->id, 'organizer_id' => $teacher->id,
            'title' => 'Live monitored exam', 'slug' => 'live-monitored-'.str()->lower(str()->random(6)),
            'status' => ExamSession::STATUS_SCHEDULED, 'starts_at' => now()->subMinute(), 'ends_at' => now()->addHours(2),
            'duration_minutes' => 60, 'max_attempts' => 1, 'passing_score' => 1,
            'security_policy' => ['fullscreen' => true, 'disconnect_threshold_seconds' => 180],
        ]);
        $candidate = ExamCandidate::query()->create(['exam_session_id' => $session->id, 'user_id' => $student->id, 'name' => $student->name, 'email' => $student->email, 'status' => ExamCandidate::STATUS_ELIGIBLE]);
        $attempt = null;
        if ($start) {
            $this->actingAs($student)->post(route('student.exam-sessions.start', $session));
            $attempt = ExamSessionAttempt::query()->where('exam_candidate_id', $candidate->id)->firstOrFail();
        }

        return [$teacher, $student, $session, $attempt, $question];
    }
}
