<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Tests\TestCase;

class ExamCandidateAttemptPhaseFiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_attempt_domain_is_added_without_reusing_legacy_attempts(): void
    {
        $this->assertTrue(Schema::hasTable('exam_attempts'));
        $this->assertTrue(Schema::hasTable('exam_session_attempts'));
        $this->assertTrue(Schema::hasColumns('exam_session_attempts', ['exam_session_id', 'exam_candidate_id', 'attempt_number', 'question_order', 'answer_order']));
    }

    public function test_eligible_student_starts_idempotent_attempt_with_snapshotted_order(): void
    {
        $student = $this->createUser(['role' => 'student']);
        [$session, $questions] = $this->openSession($student);

        $first = $this->actingAs($student)->post(route('student.exam-sessions.start', $session));
        $attempt = ExamSessionAttempt::query()->firstOrFail();
        $first->assertRedirect(route('student.exam-sessions.take', $attempt));
        $this->actingAs($student)->post(route('student.exam-sessions.start', $session))->assertRedirect(route('student.exam-sessions.take', $attempt));

        $this->assertDatabaseCount('exam_session_attempts', 1);
        $this->assertEqualsCanonicalizing($questions->pluck('id')->all(), $attempt->question_order);
        $this->assertTrue($attempt->expires_at->lessThanOrEqualTo($session->ends_at));
    }

    public function test_non_candidate_and_closed_window_cannot_start(): void
    {
        $candidate = $this->createUser(['role' => 'student']);
        $outsider = $this->createUser(['role' => 'student']);
        [$session] = $this->openSession($candidate);

        $this->actingAs($outsider)->post(route('student.exam-sessions.start', $session))->assertForbidden();
        $session->update(['starts_at' => now()->addHour(), 'ends_at' => now()->addHours(2)]);
        $this->actingAs($candidate)->post(route('student.exam-sessions.start', $session))->assertSessionHasErrors('session');
        $this->assertDatabaseCount('exam_session_attempts', 0);
    }

    public function test_attempt_limit_and_candidate_extra_time_are_enforced(): void
    {
        $student = $this->createUser(['role' => 'student']);
        [$session] = $this->openSession($student, ['duration_minutes' => 30, 'max_attempts' => 1]);
        $candidate = $session->candidates()->firstOrFail();
        $candidate->update(['extra_time_minutes' => 15]);

        $this->actingAs($student)->post(route('student.exam-sessions.start', $session))->assertRedirect();
        $attempt = ExamSessionAttempt::query()->firstOrFail();
        $this->assertEquals(45, $attempt->started_at->diffInMinutes($attempt->expires_at));
        $attempt->update(['status' => ExamSessionAttempt::STATUS_SUBMITTED, 'submitted_at' => now()]);

        $this->actingAs($student)->post(route('student.exam-sessions.start', $session))->assertSessionHasErrors('session');
        $this->assertDatabaseCount('exam_session_attempts', 1);
    }

    public function test_student_workspace_and_attempt_are_owner_scoped_and_localized(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $other = $this->createUser(['role' => 'student']);
        [$session] = $this->openSession($student);

        $this->actingAs($student)->get(route('student.exam-sessions.index'))->assertOk()
            ->assertSee(__('student-exam::app.session_workspace.title'))->assertSee($session->title);
        $this->actingAs($student)->post(route('student.exam-sessions.start', $session));
        $attempt = ExamSessionAttempt::query()->firstOrFail();
        $this->actingAs($student)->get(route('student.exam-sessions.take', $attempt))->assertOk()->assertSee($session->title);
        $this->actingAs($other)->get(route('student.exam-sessions.take', $attempt))->assertForbidden();
    }

    private function openSession(User $student, array $overrides = []): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $template = ExamTemplate::query()->create(['owner_id' => $teacher->id, 'title' => 'Template', 'slug' => 'template-'.str()->lower(str()->random(6)), 'status' => 'ready', 'ready_at' => now()]);
        $version = ExamTemplateVersion::query()->create(['exam_template_id' => $template->id, 'created_by' => $teacher->id, 'version' => 1, 'title' => 'Template', 'total_questions' => 2, 'total_points' => 2, 'locked_at' => now()]);
        $questions = collect([
            ExamTemplateQuestion::query()->create(['exam_template_version_id' => $version->id, 'sort_order' => 1, 'type' => 'single_choice', 'content' => 'Question one', 'options' => [['key' => 'A', 'text' => 'First'], ['key' => 'B', 'text' => 'Second']], 'correct_answers' => ['A'], 'points' => 1]),
            ExamTemplateQuestion::query()->create(['exam_template_version_id' => $version->id, 'sort_order' => 2, 'type' => 'true_false', 'content' => 'Question two', 'options' => [['key' => 'true', 'text' => 'True'], ['key' => 'false', 'text' => 'False']], 'correct_answers' => ['true'], 'points' => 1]),
        ]);
        $session = ExamSession::query()->create(array_merge(['exam_template_version_id' => $version->id, 'organizer_id' => $teacher->id, 'title' => 'Open final exam', 'slug' => 'open-'.str()->lower(str()->random(6)), 'status' => 'scheduled', 'starts_at' => now()->subMinute(), 'ends_at' => now()->addHours(2), 'duration_minutes' => 60, 'max_attempts' => 1, 'passing_score' => 1, 'shuffle_questions' => true, 'shuffle_answers' => true], $overrides));
        ExamCandidate::query()->create(['exam_session_id' => $session->id, 'user_id' => $student->id, 'name' => $student->name, 'email' => $student->email, 'status' => ExamCandidate::STATUS_ELIGIBLE]);

        return [$session, $questions];
    }
}
