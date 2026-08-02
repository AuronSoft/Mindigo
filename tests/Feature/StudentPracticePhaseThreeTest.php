<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeRecommendation;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\StudentPractice\Models\StudentSkillProgress;
use Mindigo\StudentPractice\Services\PracticeRecommendationService;
use Mindigo\SubjectManagement\Models\Subject;
use Tests\TestCase;

class StudentPracticePhaseThreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_incorrect_streak_creates_review_path_with_easy_questions(): void
    {
        [$student, $skill] = $this->fixture();
        $questions = collect([
            $this->question($skill, 'easy'),
            $this->question($skill, 'medium'),
            $this->question($skill, 'hard'),
        ]);
        $attempt = $this->startSkillSession($student, $skill, 3);
        $this->answerAndComplete($student, $attempt, $questions, false);

        $progress = StudentSkillProgress::query()->sole();
        $this->assertSame('novice', $progress->mastery_level);
        $this->assertSame('easy', $progress->recommended_difficulty);
        $this->assertSame(3, $progress->consecutive_incorrect);
        $this->assertSame('v1', $progress->engine_version);

        $recommendation = PracticeRecommendation::query()->sole();
        $this->assertSame(PracticeRecommendation::TYPE_REVIEW, $recommendation->type);
        $this->assertSame('incorrect_streak', $recommendation->reason_code);
        $this->assertSame(100, $recommendation->priority);
    }

    public function test_consistent_correct_answers_raise_mastery_and_recommend_advancement(): void
    {
        [$student, $skill] = $this->fixture();
        $questions = collect();
        for ($index = 0; $index < 10; $index++) {
            $questions->push($this->question($skill, 'hard'));
        }
        $attempt = $this->startSkillSession($student, $skill, 10);
        $this->answerAndComplete($student, $attempt, $questions, true);

        $progress = StudentSkillProgress::query()->sole();
        $this->assertSame(100.0, $progress->mastery_score);
        $this->assertSame(50.0, $progress->confidence_score);
        $this->assertSame('mastered', $progress->mastery_level);
        $this->assertSame('hard', $progress->recommended_difficulty);
        $this->assertSame(PracticeRecommendation::TYPE_ADVANCE, PracticeRecommendation::query()->sole()->type);
    }

    public function test_adaptive_session_uses_recommended_difficulty_and_records_mastery_transition(): void
    {
        [$student, $skill] = $this->fixture();
        $easy = $this->question($skill, 'easy');
        $medium = collect([
            $this->question($skill, 'medium'),
            $this->question($skill, 'medium'),
            $this->question($skill, 'medium'),
        ]);
        StudentSkillProgress::query()->create([
            'student_id' => $student->id, 'practice_skill_id' => $skill->id,
            'mastery_score' => 68, 'mastery_level' => 'proficient', 'confidence_score' => 60,
            'recommended_difficulty' => 'medium', 'engine_version' => 'v1',
        ]);

        $this->actingAs($student)->post(route('student.practice.adaptive.start', $skill), [
            'question_count' => 3,
        ])->assertRedirect();

        $attempt = PracticeAttempt::query()->sole();
        $this->assertTrue($attempt->is_adaptive);
        $this->assertSame('adaptive_v1', $attempt->selection_strategy);
        $this->assertSame(68.0, $attempt->mastery_before);
        $this->assertSame('medium', $attempt->adaptive_context['target_difficulty']);
        $this->assertEqualsCanonicalizing($medium->pluck('id')->all(), $attempt->answers()->pluck('question_id')->all());
        $this->assertFalse($attempt->answers()->pluck('question_id')->contains($easy->id));

        $this->answerAndComplete($student, $attempt, $medium, true);
        $this->assertNotNull($attempt->fresh()->mastery_after);
    }

    public function test_adaptive_dashboard_is_private_and_explains_recommendations(): void
    {
        [$student, $skill] = $this->fixture();
        $other = $this->createUser(['role' => 'student']);
        $progress = StudentSkillProgress::query()->create([
            'student_id' => $student->id, 'practice_skill_id' => $skill->id,
            'mastery_score' => 30, 'mastery_level' => 'novice', 'confidence_score' => 20,
            'recommended_difficulty' => 'easy', 'engine_version' => 'v1',
        ]);
        app(PracticeRecommendationService::class)->refresh($progress);

        $this->actingAs($student)->get(route('student.practice.adaptive.index'))
            ->assertOk()->assertSee($skill->name)
            ->assertSee(__('student-practice::app.adaptive.reasons.low_mastery'));
        $this->actingAs($other)->get(route('student.practice.adaptive.index'))
            ->assertOk()->assertDontSee($skill->name);
    }

    public function test_adaptive_start_validates_payload_and_inactive_skill_access(): void
    {
        [$student, $skill] = $this->fixture();
        $this->actingAs($student)->post(route('student.practice.adaptive.start', $skill), [
            'question_count' => 1,
        ])->assertSessionHasErrors('question_count');

        $skill->update(['status' => PracticeSkill::STATUS_INACTIVE]);
        $this->actingAs($student)->post(route('student.practice.adaptive.start', $skill), [
            'question_count' => 5,
        ])->assertForbidden();
    }

    private function fixture(): array
    {
        $student = $this->createUser(['role' => 'student']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $subject = Subject::query()->create([
            'name' => 'Mathematics', 'code' => 'MATH', 'slug' => 'mathematics', 'status' => 'active',
        ]);
        $skill = PracticeSkill::query()->create([
            'subject_id' => $subject->id, 'created_by' => $teacher->id,
            'code' => 'MATH.ADAPTIVE', 'name' => 'Adaptive algebra', 'status' => PracticeSkill::STATUS_ACTIVE,
        ]);

        return [$student, $skill];
    }

    private function question(PracticeSkill $skill, string $difficulty): Question
    {
        $question = Question::factory()->create([
            'subject' => 'Mathematics', 'difficulty' => $difficulty, 'status' => 'approved',
            'practice_status' => Question::PRACTICE_READY, 'type' => 'single_choice', 'correct_answers' => ['A'],
        ]);
        $skill->questions()->attach($question->id, ['is_primary' => true, 'weight' => 100]);

        return $question;
    }

    private function startSkillSession(User $student, PracticeSkill $skill, int $count): PracticeAttempt
    {
        $this->actingAs($student)->post(route('student.practice.skills.start', $skill), [
            'question_count' => $count,
        ])->assertRedirect();

        return PracticeAttempt::query()->latest('id')->firstOrFail();
    }

    private function answerAndComplete(User $student, PracticeAttempt $attempt, $questions, bool $correct): void
    {
        foreach ($questions as $question) {
            $this->actingAs($student)->postJson(route('student.practice.submit-answer', $attempt), [
                'question_id' => $question->id,
                'answer' => ['choice' => $correct ? 'A' : 'B'],
            ])->assertOk();
        }
        $this->actingAs($student)->post(route('student.practice.complete', $attempt))->assertRedirect();
    }
}
