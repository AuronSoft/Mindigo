<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Mindigo\Auth\Models\User;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeLearningInsight;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\StudentPractice\Models\StudentSkillProgress;
use Mindigo\SubjectManagement\Models\Subject;
use Tests\TestCase;

class StudentPracticePhaseFourTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_private_learning_analytics_and_generated_insights(): void
    {
        [$student, $strong, $weak] = $this->fixture();
        $this->progress($student, $strong, 90, 90);
        $this->progress($student, $weak, 35, 40);
        $this->attempt($student, $strong, 90, now()->subDays(3));
        $this->attempt($student, $weak, 40, now()->subDay());

        $this->actingAs($student)->get(route('student.practice.analytics.index'))
            ->assertOk()
            ->assertSee($strong->name)
            ->assertSee($weak->name)
            ->assertSee(__('student-practice::app.analytics.title'));

        $this->assertDatabaseHas('student_practice_insights', [
            'student_id' => $student->id,
            'practice_skill_id' => $strong->id,
            'insight_code' => 'strong_skill',
            'status' => PracticeLearningInsight::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseHas('student_practice_insights', [
            'student_id' => $student->id,
            'practice_skill_id' => $weak->id,
            'insight_code' => 'weak_skill',
        ]);

        $this->actingAs($student)->get(route('student.practice.analytics.index', ['skill_id' => $weak->id]))
            ->assertOk();
        $this->assertDatabaseHas('student_practice_insights', [
            'student_id' => $student->id,
            'fingerprint' => 'strength:all:'.$strong->id,
            'status' => PracticeLearningInsight::STATUS_ACTIVE,
        ]);

        /** @var User $other */
        $other = User::factory()->create(['role' => 'student']);
        $this->actingAs($other)->get(route('student.practice.analytics.index'))
            ->assertOk()
            ->assertViewHas('overview', fn (array $overview): bool => $overview['attempts'] === 0)
            ->assertViewHas('skills', fn ($skills): bool => $skills->isEmpty())
            ->assertViewHas('insights', fn ($insights): bool => $insights->isEmpty());
    }

    public function test_period_and_skill_filters_use_only_matching_attempts(): void
    {
        [$student, $strong, $weak] = $this->fixture();
        $this->progress($student, $strong, 80, 80);
        $this->progress($student, $weak, 50, 50);
        $this->attempt($student, $strong, 80, now()->subDays(2));
        $this->attempt($student, $weak, 50, now()->subDays(40));

        $response = $this->actingAs($student)->get(route('student.practice.analytics.index', [
            'period' => '7', 'skill_id' => $strong->id,
        ]));
        $response->assertOk()->assertViewHas('overview', fn (array $overview): bool => $overview['attempts'] === 1)
            ->assertViewHas('skills', fn ($skills): bool => $skills->count() === 1 && $skills->first()['id'] === $strong->id);

        $this->actingAs($student)->get(route('student.practice.analytics.index', ['period' => 'invalid']))
            ->assertSessionHasErrors('period');
        $this->actingAs($student)->get(route('student.practice.analytics.index', ['skill_id' => 999999]))
            ->assertSessionHasErrors('skill_id');
    }

    public function test_analytics_detects_improving_score_trend(): void
    {
        [$student, $skill] = $this->fixture();
        $this->progress($student, $skill, 70, 70);
        $this->attempt($student, $skill, 40, now()->subDays(10));
        $this->attempt($student, $skill, 50, now()->subDays(9));
        $this->attempt($student, $skill, 80, now()->subDays(2));
        $this->attempt($student, $skill, 90, now()->subDay());

        $this->actingAs($student)->get(route('student.practice.analytics.index', ['period' => '30']))
            ->assertOk()
            ->assertViewHas('improvement', fn (array $trend): bool => $trend['direction'] === 'improving' && $trend['change'] === 40.0);

        $this->assertDatabaseHas('student_practice_insights', [
            'student_id' => $student->id,
            'fingerprint' => 'trend:all:improving',
            'insight_code' => 'trend_improving',
        ]);
    }

    public function test_insight_policy_prevents_cross_student_access(): void
    {
        [$student, $skill] = $this->fixture();
        /** @var User $other */
        $other = User::factory()->create(['role' => 'student']);
        $insight = PracticeLearningInsight::query()->create([
            'student_id' => $student->id,
            'practice_skill_id' => $skill->id,
            'fingerprint' => 'strength:'.$skill->id,
            'type' => 'strength',
            'insight_code' => 'strong_skill',
            'metrics' => ['mastery_score' => 90],
            'generated_at' => now(),
        ]);

        $this->assertTrue(Gate::forUser($student)->allows('view', $insight));
        $this->assertFalse(Gate::forUser($other)->allows('view', $insight));
    }

    public function test_student_dashboard_links_to_practice_analytics(): void
    {
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee(route('student.practice.analytics.index'), false)
            ->assertSee(__('student-dashboard::app.practice_analytics'));
    }

    private function fixture(): array
    {
        /** @var User $student */
        $student = User::factory()->create(['role' => 'student']);
        /** @var User $teacher */
        $teacher = User::factory()->create(['role' => 'teacher']);
        $subject = Subject::query()->create([
            'name' => 'Mathematics', 'code' => 'MATH', 'slug' => 'mathematics', 'status' => 'active',
        ]);
        $strong = PracticeSkill::query()->create([
            'subject_id' => $subject->id, 'created_by' => $teacher->id,
            'code' => 'MATH.STRONG', 'name' => 'Strong algebra', 'status' => PracticeSkill::STATUS_ACTIVE,
        ]);
        $weak = PracticeSkill::query()->create([
            'subject_id' => $subject->id, 'created_by' => $teacher->id,
            'code' => 'MATH.WEAK', 'name' => 'Weak geometry', 'status' => PracticeSkill::STATUS_ACTIVE,
        ]);

        return [$student, $strong, $weak];
    }

    private function progress(User $student, PracticeSkill $skill, float $mastery, float $accuracy): void
    {
        StudentSkillProgress::query()->create([
            'student_id' => $student->id,
            'practice_skill_id' => $skill->id,
            'completed_attempts' => 1,
            'total_questions' => 10,
            'correct_answers' => (int) round($accuracy / 10),
            'accuracy' => $accuracy,
            'average_score' => $accuracy,
            'best_score' => $accuracy,
            'mastery_score' => $mastery,
            'mastery_level' => $mastery >= 85 ? 'mastered' : ($mastery >= 65 ? 'proficient' : 'novice'),
            'confidence_score' => 50,
            'recommended_difficulty' => $mastery >= 80 ? 'hard' : 'easy',
            'engine_version' => 'v1',
            'last_practiced_at' => now(),
        ]);
    }

    private function attempt(User $student, PracticeSkill $skill, int $score, $completedAt): PracticeAttempt
    {
        return PracticeAttempt::query()->create([
            'student_id' => $student->id,
            'practice_skill_id' => $skill->id,
            'mode' => 'skill',
            'total_questions' => 10,
            'correct_answers' => (int) round($score / 10),
            'score' => $score,
            'status' => PracticeAttempt::STATUS_COMPLETED,
            'started_at' => $completedAt->copy()->subMinutes(10),
            'last_activity_at' => $completedAt,
            'completed_at' => $completedAt,
        ]);
    }
}
