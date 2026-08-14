<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Tests\TestCase;

class ExamAttemptLifecyclePhaseSixTest extends TestCase
{
    use RefreshDatabase;

    public function test_answer_schema_extends_new_attempt_domain(): void
    {
        $this->assertTrue(Schema::hasTable('exam_session_attempt_answers'));
        $this->assertTrue(Schema::hasColumns('exam_session_attempts', ['score', 'max_score', 'percentage', 'passed', 'needs_review']));
    }

    public function test_student_autosaves_only_questions_in_owned_active_attempt(): void
    {
        [$student, $session, $questions] = $this->fixture();
        $this->actingAs($student)->post(route('student.exam-sessions.start', $session));
        $attempt = ExamSessionAttempt::query()->firstOrFail();

        $this->actingAs($student)->postJson(route('student.exam-sessions.autosave', $attempt), ['question_id' => $questions[0]->id, 'answer' => ['A']])->assertOk();
        $this->assertDatabaseHas('exam_session_attempt_answers', ['exam_session_attempt_id' => $attempt->id, 'exam_template_question_id' => $questions[0]->id]);
        $this->actingAs($student)->postJson(route('student.exam-sessions.autosave', $attempt), ['question_id' => 999999, 'answer' => ['A']])->assertUnprocessable();

        $other = $this->createUser(['role' => 'student']);
        $this->actingAs($other)->postJson(route('student.exam-sessions.autosave', $attempt), ['question_id' => $questions[0]->id, 'answer' => ['A']])->assertForbidden();
    }

    public function test_heartbeat_and_security_events_stop_when_attempt_expires(): void
    {
        [$student, $session] = $this->fixture();
        $this->actingAs($student)->post(route('student.exam-sessions.start', $session));
        $attempt = ExamSessionAttempt::query()->firstOrFail();

        $this->travel(1)->minute();
        $this->actingAs($student)->postJson(route('student.exam-sessions.heartbeat', $attempt))->assertOk();
        $this->actingAs($student)->postJson(route('student.exam-sessions.security-event', $attempt), ['type' => 'tab_hidden'])->assertOk();
        $this->assertSame('tab_hidden', $attempt->fresh()->security_events[0]['type']);

        $attempt->update(['expires_at' => now()->subSecond()]);
        $this->actingAs($student)->postJson(route('student.exam-sessions.heartbeat', $attempt))->assertStatus(409);
        $this->assertSame(ExamSessionAttempt::STATUS_EXPIRED, $attempt->fresh()->status);
    }

    public function test_submission_is_idempotent_and_auto_grades_objective_answers(): void
    {
        [$student, $session, $questions] = $this->fixture(false);
        $this->actingAs($student)->post(route('student.exam-sessions.start', $session));
        $attempt = ExamSessionAttempt::query()->firstOrFail();
        $payload = ['answers' => [$questions[0]->id => ['A'], $questions[1]->id => ['false']]];

        $this->actingAs($student)->post(route('student.exam-sessions.submit', $attempt), $payload)->assertRedirect(route('student.exam-sessions.result', $attempt));
        $this->actingAs($student)->post(route('student.exam-sessions.submit', $attempt), $payload)->assertRedirect(route('student.exam-sessions.result', $attempt));

        $attempt->refresh();
        $this->assertSame(ExamSessionAttempt::STATUS_SUBMITTED, $attempt->status);
        $this->assertSame('2.00', $attempt->score);
        $this->assertSame('5.00', $attempt->max_score);
        $this->assertFalse($attempt->passed);
        $this->assertFalse($attempt->needs_review);
        $this->assertDatabaseCount('exam_session_attempt_answers', 2);
    }

    public function test_essay_submission_waits_for_teacher_review_and_result_policy_is_respected(): void
    {
        [$student, $session, $questions] = $this->fixture(true, 'after_release');
        $this->actingAs($student)->post(route('student.exam-sessions.start', $session));
        $attempt = ExamSessionAttempt::query()->firstOrFail();
        $this->actingAs($student)->post(route('student.exam-sessions.submit', $attempt), ['answers' => [$questions[0]->id => ['A'], $questions[1]->id => 'Essay response']]);

        $attempt->refresh();
        $this->assertTrue($attempt->needs_review);
        $this->assertNull($attempt->passed);
        $this->assertDatabaseHas('exam_session_attempt_answers', ['exam_session_attempt_id' => $attempt->id, 'exam_template_question_id' => $questions[1]->id, 'needs_review' => true]);
        $this->actingAs($student)->get(route('student.exam-sessions.result', $attempt))->assertOk()
            ->assertSee(__('student-exam::app.session_workspace.pending_review'));
    }

    private function fixture(bool $essay = false, string $resultPolicy = 'immediately'): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $template = ExamTemplate::query()->create(['owner_id' => $teacher->id, 'title' => 'Lifecycle template', 'slug' => 'lifecycle-'.str()->lower(str()->random(6)), 'status' => 'ready', 'ready_at' => now()]);
        $version = ExamTemplateVersion::query()->create(['exam_template_id' => $template->id, 'created_by' => $teacher->id, 'version' => 1, 'title' => $template->title, 'total_questions' => 2, 'total_points' => 5, 'locked_at' => now()]);
        $questions = collect([
            ExamTemplateQuestion::query()->create(['exam_template_version_id' => $version->id, 'sort_order' => 1, 'type' => 'single_choice', 'content' => 'Objective', 'options' => [['key' => 'A', 'text' => 'Correct'], ['key' => 'B', 'text' => 'Wrong']], 'correct_answers' => ['A'], 'points' => 2]),
            ExamTemplateQuestion::query()->create(['exam_template_version_id' => $version->id, 'sort_order' => 2, 'type' => $essay ? 'essay' : 'true_false', 'content' => 'Second', 'options' => $essay ? [] : [['key' => 'true', 'text' => 'True'], ['key' => 'false', 'text' => 'False']], 'correct_answers' => $essay ? [] : ['true'], 'points' => 3]),
        ]);
        $session = ExamSession::query()->create(['exam_template_version_id' => $version->id, 'organizer_id' => $teacher->id, 'title' => 'Lifecycle exam', 'slug' => 'lifecycle-exam-'.str()->lower(str()->random(6)), 'status' => 'scheduled', 'starts_at' => now()->subMinute(), 'ends_at' => now()->addHours(2), 'duration_minutes' => 60, 'max_attempts' => 1, 'passing_score' => 4, 'result_policy' => $resultPolicy, 'shuffle_questions' => false, 'shuffle_answers' => false]);
        ExamCandidate::query()->create(['exam_session_id' => $session->id, 'user_id' => $student->id, 'name' => $student->name, 'email' => $student->email, 'status' => ExamCandidate::STATUS_ELIGIBLE]);

        return [$student, $session, $questions];
    }
}
