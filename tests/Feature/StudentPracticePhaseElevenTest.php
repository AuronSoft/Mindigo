<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeSet;
use Tests\TestCase;

class StudentPracticePhaseElevenTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_eleven_schema_keeps_practice_sets_outside_official_exams(): void
    {
        $this->assertTrue(\Schema::hasColumns('learning_practice_sets', [
            'description', 'share_token', 'is_shared',
        ]));
        $this->assertFalse(\Schema::hasColumn('learning_practice_sets', 'exam_session_id'));
    }

    public function test_student_creates_and_starts_a_private_saved_set(): void
    {
        $student = $this->student();
        Question::factory()->count(4)->create([
            'subject' => 'Mathematics',
            'status' => 'approved',
        ]);

        $this->actingAs($student)->post(route('student.practice.sets.store'), [
            'title' => 'Algebra review',
            'description' => 'Private revision',
            'subject' => 'Mathematics',
            'source' => 'manual',
            'question_count' => 3,
        ])->assertRedirect();

        $set = PracticeSet::query()->sole();
        $this->assertSame($student->id, $set->creator_id);
        $this->assertNull($set->classroom_id);
        $this->assertCount(3, $set->questions);

        $this->actingAs($student)->post(route('student.practice.sets.start', $set))->assertRedirect();
        $this->assertDatabaseHas('student_practice_attempts', [
            'student_id' => $student->id,
            'practice_set_id' => $set->id,
            'status' => PracticeAttempt::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_student_builds_a_set_from_previous_mistakes(): void
    {
        $student = $this->student();
        $wrong = Question::factory()->create(['status' => 'approved']);
        $other = Question::factory()->create(['status' => 'approved']);
        $attempt = PracticeAttempt::query()->create([
            'student_id' => $student->id, 'mode' => 'mixed', 'total_questions' => 1,
            'correct_answers' => 0, 'score' => 0, 'status' => PracticeAttempt::STATUS_COMPLETED,
            'started_at' => now()->subMinute(), 'last_activity_at' => now(), 'completed_at' => now(),
        ]);
        $attempt->answers()->create(['question_id' => $wrong->id, 'is_correct' => false, 'points' => 0]);

        $this->actingAs($student)->post(route('student.practice.sets.store'), [
            'title' => 'My mistakes', 'source' => 'mistakes', 'question_count' => 10,
        ])->assertRedirect();

        $set = PracticeSet::query()->sole();
        $this->assertTrue($set->questions->contains($wrong));
        $this->assertFalse($set->questions->contains($other));
    }

    public function test_shared_set_is_previewable_and_startable_by_another_student(): void
    {
        $owner = $this->student();
        $guestStudent = $this->student();
        $question = Question::factory()->create(['status' => 'approved']);
        $set = PracticeSet::query()->create([
            'creator_id' => $owner->id, 'title' => 'Shared review', 'source' => 'manual',
            'status' => PracticeSet::STATUS_READY,
        ]);
        $set->questions()->attach($question->id, ['position' => 1]);

        $this->actingAs($owner)->patch(route('student.practice.sets.share', $set), ['enabled' => 1])
            ->assertRedirect();
        $set->refresh();
        $this->get(route('practice.shared', $set->share_token))->assertOk()->assertSee('Shared review');
        $this->actingAs($guestStudent)->post(route('student.practice.sets.start', $set))->assertRedirect();
    }

    public function test_admin_cannot_manage_student_practice_sets(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)->get(route('student.practice.sets.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_exam_builder_uses_neutral_footer_and_balanced_actions(): void
    {
        $css = file_get_contents(base_path('packages/Mindigo/ExamManagement/src/resources/css/app.css'));
        $view = file_get_contents(base_path('packages/Mindigo/ExamManagement/src/resources/views/partials/form.blade.php'));

        $this->assertStringContainsString('border-slate-200 bg-white p-5 shadow-sm', $css);
        $this->assertStringNotContainsString('border-t-0 border-green-100', $css);
        $this->assertStringContainsString('exam-studio-submit-actions', $view);
        $this->assertStringContainsString('exam-save-chip', $view);
        $this->assertStringContainsString('exam-review-button', $view);
        $this->assertStringContainsString('data-exam-wizard', $view);
        $this->assertStringContainsString('data-exam-part="review"', $view);
        $this->assertStringContainsString("route('teacher.questions.import')", $view);
    }

    private function student(): User
    {
        return $this->createUser(['role' => 'student']);
    }
}
