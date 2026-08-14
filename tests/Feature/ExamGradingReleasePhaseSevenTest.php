<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Mindigo\Notification\Notifications\ExamResultReleased;
use Tests\TestCase;

class ExamGradingReleasePhaseSevenTest extends TestCase
{
    use RefreshDatabase;

    public function test_grading_schema_records_review_and_release_actors(): void
    {
        $this->assertTrue(Schema::hasColumns('exam_session_attempts', ['reviewed_by', 'reviewed_at', 'released_by', 'released_at']));
        $this->assertTrue(Schema::hasColumns('exam_session_attempt_answers', ['reviewed_by', 'reviewed_at']));
    }

    public function test_only_session_organizer_can_open_grading_queue(): void
    {
        [$teacher, $student, $session] = $this->fixture();
        $otherTeacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('teacher.exam-sessions.grading.index', $session))
            ->assertOk()
            ->assertSee(__('Mindigo-exam-management::app.grading.title'));
        $attempt = ExamSessionAttempt::query()->firstOrFail();
        $this->actingAs($teacher)->get(route('teacher.exam-sessions.grading.show', [$session, $attempt]))
            ->assertOk()
            ->assertSee(__('Mindigo-exam-management::app.grading.review_title'));
        $this->actingAs($otherTeacher)->get(route('teacher.exam-sessions.grading.index', $session))->assertForbidden();
        $this->actingAs($student)->get(route('teacher.exam-sessions.grading.index', $session))->assertRedirect();
    }

    public function test_teacher_grades_essay_and_attempt_totals_are_recalculated(): void
    {
        [$teacher, , $session, $attempt, $essayAnswer] = $this->fixture();

        $this->actingAs($teacher)->put(route('teacher.exam-sessions.grading.answers.update', [$session, $attempt, $essayAnswer]), [
            'points_awarded' => 2.5,
            'feedback' => 'Clear reasoning.',
        ])->assertRedirect();

        $essayAnswer->refresh();
        $attempt->refresh();
        $this->assertFalse($essayAnswer->needs_review);
        $this->assertSame('2.50', $essayAnswer->points_awarded);
        $this->assertSame($teacher->id, $essayAnswer->reviewed_by);
        $this->assertFalse($attempt->needs_review);
        $this->assertSame('4.50', $attempt->score);
        $this->assertSame('90.00', $attempt->percentage);
        $this->assertTrue($attempt->passed);
        $this->assertSame($teacher->id, $attempt->reviewed_by);
    }

    public function test_teacher_cannot_award_more_than_question_points_or_release_pending_review(): void
    {
        [$teacher, , $session, $attempt, $essayAnswer] = $this->fixture();

        $this->actingAs($teacher)->put(route('teacher.exam-sessions.grading.answers.update', [$session, $attempt, $essayAnswer]), [
            'points_awarded' => 4,
        ])->assertSessionHasErrors('points_awarded');
        $this->actingAs($teacher)->post(route('teacher.exam-sessions.grading.release', [$session, $attempt]))
            ->assertSessionHasErrors('attempt');
        $this->assertNull($attempt->fresh()->released_at);
    }

    public function test_released_result_becomes_visible_to_student_for_after_release_policy(): void
    {
        Notification::fake();
        [$teacher, $student, $session, $attempt, $essayAnswer] = $this->fixture();

        $this->actingAs($student)->get(route('student.exam-sessions.result', $attempt))
            ->assertOk()->assertSee(__('student-exam::app.session_workspace.pending_review'));

        $this->actingAs($teacher)->put(route('teacher.exam-sessions.grading.answers.update', [$session, $attempt, $essayAnswer]), ['points_awarded' => 2]);
        $this->actingAs($student)->get(route('student.exam-sessions.result', $attempt))
            ->assertOk()->assertSee(__('student-exam::app.session_workspace.result_hidden'));

        $this->actingAs($teacher)->post(route('teacher.exam-sessions.grading.release', [$session, $attempt]))->assertRedirect();
        $this->assertSame($teacher->id, $attempt->fresh()->released_by);
        Notification::assertSentTo($student, ExamResultReleased::class, fn (ExamResultReleased $notification) => $notification->attemptId === $attempt->id);
        $this->actingAs($student)->get(route('student.exam-sessions.result', $attempt))
            ->assertOk()->assertSee(__('student-exam::app.session_workspace.score', ['score' => '4.00', 'max' => '5.00']));
    }

    private function fixture(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $template = ExamTemplate::query()->create(['owner_id' => $teacher->id, 'title' => 'Grading template', 'slug' => 'grading-'.str()->lower(str()->random(6)), 'status' => 'ready', 'ready_at' => now()]);
        $version = ExamTemplateVersion::query()->create(['exam_template_id' => $template->id, 'created_by' => $teacher->id, 'version' => 1, 'title' => $template->title, 'total_questions' => 2, 'total_points' => 5, 'locked_at' => now()]);
        $objective = ExamTemplateQuestion::query()->create(['exam_template_version_id' => $version->id, 'sort_order' => 1, 'type' => 'single_choice', 'content' => 'Objective question', 'options' => [['key' => 'A', 'text' => 'Correct']], 'correct_answers' => ['A'], 'points' => 2]);
        $essay = ExamTemplateQuestion::query()->create(['exam_template_version_id' => $version->id, 'sort_order' => 2, 'type' => 'essay', 'content' => 'Essay question', 'options' => [], 'correct_answers' => [], 'points' => 3]);
        $session = ExamSession::query()->create(['exam_template_version_id' => $version->id, 'organizer_id' => $teacher->id, 'title' => 'Grading session', 'slug' => 'grading-session-'.str()->lower(str()->random(6)), 'status' => 'ended', 'starts_at' => now()->subHours(2), 'ends_at' => now()->subHour(), 'duration_minutes' => 60, 'max_attempts' => 1, 'passing_score' => 4, 'result_policy' => 'after_release', 'shuffle_questions' => false, 'shuffle_answers' => false]);
        $candidate = ExamCandidate::query()->create(['exam_session_id' => $session->id, 'user_id' => $student->id, 'name' => $student->name, 'email' => $student->email, 'status' => ExamCandidate::STATUS_ELIGIBLE]);
        $attempt = ExamSessionAttempt::query()->create(['exam_session_id' => $session->id, 'exam_candidate_id' => $candidate->id, 'user_id' => $student->id, 'attempt_number' => 1, 'status' => ExamSessionAttempt::STATUS_SUBMITTED, 'started_at' => now()->subMinutes(50), 'expires_at' => now()->subMinutes(10), 'last_activity_at' => now()->subMinutes(10), 'submitted_at' => now()->subMinutes(10), 'question_order' => [$objective->id, $essay->id], 'answer_order' => [], 'security_events' => [], 'score' => 2, 'max_score' => 5, 'percentage' => 40, 'passed' => null, 'needs_review' => true]);
        ExamSessionAttemptAnswer::query()->create(['exam_session_attempt_id' => $attempt->id, 'exam_template_question_id' => $objective->id, 'type' => 'single_choice', 'answer' => ['A'], 'is_correct' => true, 'points_awarded' => 2, 'needs_review' => false]);
        $essayAnswer = ExamSessionAttemptAnswer::query()->create(['exam_session_attempt_id' => $attempt->id, 'exam_template_question_id' => $essay->id, 'type' => 'essay', 'answer' => ['Essay response'], 'is_correct' => null, 'points_awarded' => 0, 'needs_review' => true]);

        return [$teacher, $student, $session, $attempt, $essayAnswer];
    }
}
