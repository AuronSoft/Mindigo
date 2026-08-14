<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamGradeAppeal;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Tests\TestCase;

class ExamAdvancedGradingPhaseTenTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_ten_schema_supports_advanced_grading(): void
    {
        $this->assertTrue(Schema::hasTable('exam_grading_assignments'));
        $this->assertTrue(Schema::hasTable('exam_grade_revisions'));
        $this->assertTrue(Schema::hasTable('exam_grade_appeals'));
        $this->assertTrue(Schema::hasColumns('exam_session_attempts', ['grading_status', 'anonymous_code']));
        $this->assertTrue(Schema::hasColumn('exam_session_attempt_answers', 'rubric_scores'));
        $this->assertTrue(Schema::hasColumn('exam_template_questions', 'rubric'));
    }

    public function test_organizer_assigns_an_active_teacher_who_can_grade_but_outsider_cannot(): void
    {
        [$owner, $grader, $outsider, , $session] = $this->fixture();
        $this->actingAs($outsider)->get(route('teacher.exam-sessions.grading.index', $session))->assertForbidden();
        $this->actingAs($owner)->post(route('teacher.exam-sessions.grading.assign', $session), ['grader_id' => $grader->id])->assertRedirect();
        $this->actingAs($grader)->get(route('teacher.exam-sessions.grading.index', $session))->assertOk();
        $this->assertDatabaseHas('exam_grading_assignments', ['exam_session_id' => $session->id, 'grader_id' => $grader->id, 'assigned_by' => $owner->id]);
    }

    public function test_rubric_autosave_records_immutable_score_history_and_completes_grading(): void
    {
        [$owner, , , , $session, $attempt, $essay] = $this->fixture();
        $payload = ['points_awarded' => 0, 'rubric_scores' => [1.5, 1.5], 'feedback' => 'Clear response', 'reason' => 'Initial rubric grade'];

        $this->actingAs($owner)->putJson(route('teacher.exam-sessions.grading.answers.autosave', [$session, $attempt, $essay]), $payload)
            ->assertOk()->assertJsonPath('ok', true);

        $this->assertSame('3.00', $essay->fresh()->points_awarded);
        $this->assertDatabaseHas('exam_grade_revisions', ['exam_session_attempt_answer_id' => $essay->id, 'previous_points' => 0, 'new_points' => 3]);
        $this->assertSame(ExamSessionAttempt::GRADING_COMPLETED, $attempt->fresh()->grading_status);
    }

    public function test_anonymous_queue_hides_candidate_identity_and_supports_question_grading(): void
    {
        [$owner, , , $student, $session, $attempt, , , $essayQuestion] = $this->fixture();

        $this->actingAs($owner)->get(route('teacher.exam-sessions.grading.index', $session))
            ->assertOk()->assertSee($attempt->anonymous_code)->assertDontSee($student->email);
        $this->actingAs($owner)->get(route('teacher.exam-sessions.grading.question', [$session, $essayQuestion]))
            ->assertOk()->assertSee($attempt->anonymous_code)->assertDontSee($student->email);
    }

    public function test_bulk_regrade_updates_objective_answers_from_current_snapshot(): void
    {
        [$owner, , , , $session, , , $objective] = $this->fixture();
        $this->assertSame('0.00', $objective->points_awarded);

        $this->actingAs($owner)->post(route('teacher.exam-sessions.grading.regrade', $session))->assertRedirect();

        $this->assertSame('2.00', $objective->fresh()->points_awarded);
        $this->assertDatabaseHas('exam_grade_revisions', ['exam_session_attempt_answer_id' => $objective->id, 'reason' => 'bulk_regrade']);
    }

    public function test_released_student_can_appeal_and_assigned_grader_can_resolve(): void
    {
        Notification::fake();
        [$owner, $grader, , $student, $session, $attempt, $essay] = $this->fixture();
        $this->actingAs($owner)->post(route('teacher.exam-sessions.grading.assign', $session), ['grader_id' => $grader->id]);
        $this->actingAs($owner)->put(route('teacher.exam-sessions.grading.answers.update', [$session, $attempt, $essay]), ['points_awarded' => 2]);
        $this->actingAs($owner)->post(route('teacher.exam-sessions.grading.release', [$session, $attempt]))->assertRedirect();
        $this->actingAs($student)->post(route('student.exam-sessions.appeal', $attempt), ['reason' => 'Please review the essay rubric.'])->assertRedirect();
        $appeal = ExamGradeAppeal::query()->firstOrFail();
        $this->actingAs($grader)->post(route('teacher.exam-sessions.grading.appeals.resolve', [$session, $appeal]), ['status' => 'upheld', 'resolution' => 'Score reviewed.'])->assertRedirect();

        $this->assertSame(ExamGradeAppeal::STATUS_UPHELD, $appeal->fresh()->status);
        $this->assertSame($grader->id, $appeal->fresh()->resolved_by);
    }

    public function test_authorized_grader_exports_excel_and_pdf(): void
    {
        [$owner, , , $student, $session] = $this->fixture();
        $this->actingAs($owner)->get(route('teacher.exam-sessions.grading.export.excel', $session))->assertOk()->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8')->assertDontSee($student->email);
        $this->actingAs($owner)->get(route('teacher.exam-sessions.grading.export.pdf', $session))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    private function fixture(): array
    {
        $owner = $this->createUser(['role' => 'teacher']);
        $grader = $this->createUser(['role' => 'teacher']);
        $outsider = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $template = ExamTemplate::query()->create(['owner_id' => $owner->id, 'title' => 'Advanced grading', 'slug' => 'advanced-grading-'.str()->random(6), 'status' => 'ready', 'ready_at' => now()]);
        $version = ExamTemplateVersion::query()->create(['exam_template_id' => $template->id, 'created_by' => $owner->id, 'version' => 1, 'title' => $template->title, 'total_questions' => 2, 'total_points' => 5, 'locked_at' => now()]);
        $essayQuestion = ExamTemplateQuestion::query()->create(['exam_template_version_id' => $version->id, 'sort_order' => 1, 'type' => 'essay', 'content' => 'Explain the concept', 'correct_answers' => [], 'rubric' => [['label' => 'Accuracy', 'max_points' => 1.5], ['label' => 'Clarity', 'max_points' => 1.5]], 'points' => 3]);
        $objectiveQuestion = ExamTemplateQuestion::query()->create(['exam_template_version_id' => $version->id, 'sort_order' => 2, 'type' => 'single_choice', 'content' => 'Choose A', 'options' => [['key' => 'A', 'text' => 'A']], 'correct_answers' => ['A'], 'points' => 2]);
        $session = ExamSession::query()->create(['exam_template_version_id' => $version->id, 'organizer_id' => $owner->id, 'title' => 'Advanced grading session', 'slug' => 'advanced-session-'.str()->random(6), 'status' => ExamSession::STATUS_ENDED, 'starts_at' => now()->subHours(2), 'ends_at' => now()->subHour(), 'duration_minutes' => 60, 'max_attempts' => 1, 'passing_score' => 3, 'result_policy' => 'after_release', 'anonymous_grading' => true]);
        $candidate = ExamCandidate::query()->create(['exam_session_id' => $session->id, 'user_id' => $student->id, 'name' => $student->name, 'email' => $student->email]);
        $attempt = ExamSessionAttempt::query()->create(['exam_session_id' => $session->id, 'exam_candidate_id' => $candidate->id, 'user_id' => $student->id, 'attempt_number' => 1, 'status' => ExamSessionAttempt::STATUS_SUBMITTED, 'started_at' => now()->subHour(), 'expires_at' => now(), 'last_activity_at' => now(), 'submitted_at' => now(), 'question_order' => [$essayQuestion->id, $objectiveQuestion->id], 'score' => 0, 'max_score' => 5, 'needs_review' => true, 'grading_status' => ExamSessionAttempt::GRADING_PENDING_MANUAL, 'anonymous_code' => 'CAND-TEST001']);
        $essay = ExamSessionAttemptAnswer::query()->create(['exam_session_attempt_id' => $attempt->id, 'exam_template_question_id' => $essayQuestion->id, 'type' => 'essay', 'answer' => ['Response'], 'points_awarded' => 0, 'needs_review' => true]);
        $objective = ExamSessionAttemptAnswer::query()->create(['exam_session_attempt_id' => $attempt->id, 'exam_template_question_id' => $objectiveQuestion->id, 'type' => 'single_choice', 'answer' => ['A'], 'points_awarded' => 0, 'needs_review' => false]);

        return [$owner, $grader, $outsider, $student, $session, $attempt, $essay, $objective, $essayQuestion];
    }
}
