<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeSet;
use Tests\TestCase;

class StudentPracticePhaseZeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_open_practice_workspace_and_start_a_bounded_attempt(): void
    {
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);
        Question::factory()->count(5)->create([
            'subject' => 'Mathematics',
            'status' => 'approved',
        ]);

        $this->actingAs($student)
            ->get(route('student.practice.index'))
            ->assertOk()
            ->assertSee(__('student-practice::app.title'));

        $this->actingAs($student)->post(route('student.practice.start'), [
            'mode' => 'subject',
            'subject' => 'Mathematics',
            'question_count' => 3,
        ])->assertRedirect();

        $attempt = PracticeAttempt::query()->where('student_id', $student->id)->sole();
        $this->assertSame(PracticeAttempt::STATUS_IN_PROGRESS, $attempt->status);
        $this->assertSame(3, $attempt->total_questions);
        $this->assertNotNull($attempt->last_activity_at);
        $this->assertCount(3, $attempt->answers);

        $this->actingAs($student)
            ->get(route('student.practice.attempt', $attempt))
            ->assertOk()
            ->assertSee(__('student-practice::app.finish'));

        $this->actingAs($student)
            ->get(route('student.practice.show', $attempt->answers()->firstOrFail()->question_id))
            ->assertOk();
    }

    public function test_student_cannot_access_or_update_another_students_attempt(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create(['role' => 'student']);
        /** @var User $outsider */
        $outsider = User::factory()->create(['role' => 'student']);
        $question = Question::factory()->create(['status' => 'approved']);
        $attempt = $this->attemptFor($owner, $question);

        $this->actingAs($outsider)
            ->get(route('student.practice.attempt', $attempt))
            ->assertForbidden();

        $this->actingAs($outsider)->postJson(route('student.practice.submit-answer', $attempt), [
            'question_id' => $question->id,
            'answer' => ['choice' => 'A'],
        ])->assertForbidden();

        $this->assertNull($attempt->answers()->sole()->student_answer);
    }

    public function test_completing_practice_uses_current_answers_and_is_idempotent(): void
    {
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);
        $question = Question::factory()->create([
            'status' => 'approved',
            'type' => 'single_choice',
            'correct_answers' => ['A'],
        ]);
        $attempt = $this->attemptFor($student, $question);

        $this->actingAs($student)->postJson(route('student.practice.submit-answer', $attempt), [
            'question_id' => $question->id,
            'answer' => ['choice' => 'A'],
        ])->assertOk()->assertJsonPath('is_correct', true);

        $this->actingAs($student)
            ->post(route('student.practice.complete', $attempt))
            ->assertRedirect(route('student.practice.result', $attempt));

        $attempt->refresh();
        $this->assertSame(PracticeAttempt::STATUS_COMPLETED, $attempt->status);
        $this->assertSame(1, $attempt->correct_answers);
        $this->assertSame(100.0, $attempt->score);

        $this->actingAs($student)
            ->get(route('student.practice.result', $attempt))
            ->assertOk()
            ->assertSee('100.0%');
        $this->actingAs($student)
            ->get(route('student.practice.history'))
            ->assertOk();

        $this->actingAs($student)
            ->post(route('student.practice.complete', $attempt))
            ->assertRedirect(route('student.practice.result', $attempt));
        $this->assertSame(1, $attempt->fresh()->correct_answers);
    }

    public function test_personalized_set_uses_canonical_domain_and_links_the_attempt(): void
    {
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);
        Question::factory()->count(2)->create([
            'subject' => 'Physics',
            'status' => 'approved',
        ]);

        $this->actingAs($student)->post(route('learning-tools.personalized.store'), [
            'title' => 'Physics foundation',
            'subject' => 'Physics',
            'source' => 'manual',
            'question_count' => 2,
        ])->assertRedirect();

        $set = PracticeSet::query()->where('creator_id', $student->id)->sole();
        $this->assertSame(PracticeSet::STATUS_READY, $set->status);

        $this->actingAs($student)
            ->post(route('learning-tools.personalized.start', $set))
            ->assertRedirect();

        $this->assertDatabaseHas('student_practice_attempts', [
            'student_id' => $student->id,
            'practice_set_id' => $set->id,
            'status' => PracticeAttempt::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_practice_requests_reject_invalid_role_and_payload(): void
    {
        /** @var User $teacher */
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->postJson(route('student.practice.start'), [
            'mode' => 'mixed',
            'question_count' => 10,
        ])->assertForbidden();

        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->post(route('student.practice.start'), [
            'mode' => 'unknown',
            'question_count' => 0,
        ])->assertSessionHasErrors(['mode', 'question_count']);
    }

    private function attemptFor(User $student, Question $question): PracticeAttempt
    {
        $attempt = PracticeAttempt::query()->create([
            'student_id' => $student->id,
            'mode' => 'mixed',
            'total_questions' => 1,
            'correct_answers' => 0,
            'status' => PracticeAttempt::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);
        $attempt->answers()->create([
            'question_id' => $question->id,
            'is_correct' => false,
            'points' => 0,
        ]);

        return $attempt;
    }
}
