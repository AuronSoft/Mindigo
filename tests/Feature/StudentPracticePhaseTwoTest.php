<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\StudentPractice\Models\StudentSkillProgress;
use Mindigo\SubjectManagement\Models\Subject;
use Tests\TestCase;

class StudentPracticePhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_browse_active_skill_and_start_balanced_session(): void
    {
        [$student, $skill] = $this->fixture();
        foreach (Question::DIFFICULTIES as $difficulty) {
            $this->attachQuestion($skill, $difficulty);
        }

        $this->actingAs($student)->get(route('student.practice.skills.index'))
            ->assertOk()->assertSee($skill->name);
        $this->actingAs($student)->get(route('student.practice.skills.show', $skill))
            ->assertOk()->assertSee(__('student-practice::app.skill_practice.start_title'));
        $this->actingAs($student)->post(route('student.practice.skills.start', $skill), [
            'question_count' => 3,
        ])->assertRedirect();

        $attempt = PracticeAttempt::query()->where('student_id', $student->id)->sole();
        $this->assertSame($skill->id, $attempt->practice_skill_id);
        $this->assertSame('balanced', $attempt->selection_strategy);
        $this->assertSame(3, $attempt->question_pool_size);
        $this->assertEqualsCanonicalizing(Question::DIFFICULTIES, $attempt->answers()->pluck('difficulty_snapshot')->all());
        $this->assertNotNull($attempt->answers()->firstOrFail()->question_snapshot['content']);
    }

    public function test_skill_session_excludes_questions_from_another_skill_and_rejects_inactive_skill(): void
    {
        [$student, $skill, $teacher, $subject] = $this->fixture();
        $selected = $this->attachQuestion($skill, 'easy');
        $other = PracticeSkill::query()->create([
            'subject_id' => $subject->id, 'created_by' => $teacher->id, 'code' => 'MATH.OTHER',
            'name' => 'Other skill', 'status' => PracticeSkill::STATUS_ACTIVE,
        ]);
        $outside = $this->attachQuestion($other, 'hard');

        $this->actingAs($student)->post(route('student.practice.skills.start', $skill), ['question_count' => 2])
            ->assertRedirect();
        $attempt = PracticeAttempt::query()->sole();
        $this->assertSame([$selected->id], $attempt->answers()->pluck('question_id')->all());
        $this->assertNotSame($outside->id, $attempt->answers()->sole()->question_id);

        $other->update(['status' => PracticeSkill::STATUS_INACTIVE]);
        $this->actingAs($student)->get(route('student.practice.skills.show', $other))->assertForbidden();
        $this->actingAs($student)->post(route('student.practice.skills.start', $other), ['question_count' => 1])->assertForbidden();
    }

    public function test_completion_builds_idempotent_skill_progress_and_answer_evidence(): void
    {
        [$student, $skill] = $this->fixture();
        $question = $this->attachQuestion($skill, 'medium', ['correct_answers' => ['A']]);
        $this->actingAs($student)->post(route('student.practice.skills.start', $skill), ['question_count' => 1]);
        $attempt = PracticeAttempt::query()->sole();

        $this->actingAs($student)->postJson(route('student.practice.submit-answer', $attempt), [
            'question_id' => $question->id, 'answer' => ['choice' => 'A'],
        ])->assertOk()->assertJsonPath('is_correct', true);
        $answer = $attempt->answers()->sole();
        $this->assertSame(1, $answer->answer_revision);
        $this->assertNotNull($answer->answered_at);

        $this->actingAs($student)->post(route('student.practice.complete', $attempt))->assertRedirect();
        $progress = StudentSkillProgress::query()->sole();
        $this->assertSame(1, $progress->completed_attempts);
        $this->assertSame(1, $progress->total_questions);
        $this->assertSame(100.0, $progress->accuracy);
        $this->assertSame(100.0, $progress->best_score);

        $this->actingAs($student)->get(route('student.practice.skills.show', $skill))
            ->assertOk()->assertSee('100.0%');
    }

    public function test_new_skill_session_avoids_recent_questions_when_pool_allows(): void
    {
        [$student, $skill] = $this->fixture();
        $this->attachQuestion($skill, 'easy');
        $this->attachQuestion($skill, 'easy');

        $this->actingAs($student)->post(route('student.practice.skills.start', $skill), ['question_count' => 1]);
        $first = PracticeAttempt::query()->oldest('id')->firstOrFail();
        $firstQuestion = $first->answers()->sole()->question_id;

        $this->actingAs($student)->post(route('student.practice.skills.start', $skill), ['question_count' => 1]);
        $second = PracticeAttempt::query()->latest('id')->firstOrFail();

        $this->assertNotSame($firstQuestion, $second->answers()->sole()->question_id);
    }

    public function test_each_student_only_sees_their_own_skill_statistics(): void
    {
        [$student, $skill] = $this->fixture();
        $other = User::factory()->create(['role' => 'student']);
        StudentSkillProgress::query()->create([
            'student_id' => $other->id, 'practice_skill_id' => $skill->id,
            'completed_attempts' => 9, 'total_questions' => 90, 'correct_answers' => 81,
            'accuracy' => 90, 'average_score' => 90, 'best_score' => 100,
        ]);

        $this->actingAs($student)->get(route('student.practice.skills.show', $skill))
            ->assertOk()->assertDontSee('90.0%');
    }

    private function fixture(): array
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $subject = Subject::query()->create([
            'name' => 'Mathematics', 'code' => 'MATH', 'slug' => 'mathematics', 'status' => 'active',
        ]);
        $skill = PracticeSkill::query()->create([
            'subject_id' => $subject->id, 'created_by' => $teacher->id, 'code' => 'MATH.ALGEBRA',
            'name' => 'Algebra', 'status' => PracticeSkill::STATUS_ACTIVE,
        ]);

        return [$student, $skill, $teacher, $subject];
    }

    private function attachQuestion(PracticeSkill $skill, string $difficulty, array $attributes = []): Question
    {
        $question = Question::factory()->create(array_merge([
            'subject' => 'Mathematics', 'difficulty' => $difficulty, 'status' => 'approved',
            'practice_status' => Question::PRACTICE_READY,
        ], $attributes));
        $skill->questions()->attach($question->id, ['is_primary' => true, 'weight' => 100]);

        return $question;
    }
}
