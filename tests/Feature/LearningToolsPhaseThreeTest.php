<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\LearningTools\Models\PersonalizedPracticeSet;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAnswer;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Tests\TestCase;

class LearningToolsPhaseThreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_and_start_personalized_practice(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        Question::factory()->count(3)->create(['subject' => 'Mathematics', 'topic' => 'Functions', 'status' => 'approved']);

        $this->actingAs($student)->post(route('learning-tools.personalized.store'), [
            'title' => 'Function review', 'subject' => 'Mathematics', 'topic' => 'Functions',
            'source' => 'manual', 'question_count' => 2,
        ])->assertRedirect();

        $set = PersonalizedPracticeSet::where('creator_id', $student->id)->sole();
        $this->assertCount(2, $set->questions);
        $this->actingAs($student)->post(route('learning-tools.personalized.start', $set))->assertRedirect();
        $attempt = PracticeAttempt::where('student_id', $student->id)->sole();
        $this->assertSame(2, $attempt->answers()->count());
    }

    public function test_teacher_can_only_assign_practice_to_own_classroom(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        $classroom = $this->classroom($otherTeacher);
        Question::factory()->create(['status' => 'approved']);

        $this->actingAs($teacher)->post(route('learning-tools.personalized.store'), [
            'title' => 'Unauthorized assignment', 'source' => 'manual', 'question_count' => 1,
            'classroom_id' => $classroom->id,
        ])->assertForbidden();
    }

    public function test_mistake_notebook_and_gap_analysis_use_completed_practice_data(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $question = Question::factory()->create(['subject' => 'Physics', 'topic' => 'Motion', 'status' => 'approved']);
        $attempt = PracticeAttempt::create([
            'student_id' => $student->id, 'mode' => 'topic', 'subject' => 'Physics', 'topic' => 'Motion',
            'total_questions' => 1, 'correct_answers' => 0, 'score' => 0, 'status' => PracticeAttempt::STATUS_COMPLETED, 'started_at' => now()->subMinute(), 'completed_at' => now(),
        ]);
        $answer = PracticeAnswer::create(['attempt_id' => $attempt->id, 'question_id' => $question->id, 'student_answer' => ['choice' => 'B'], 'is_correct' => false, 'points' => 0]);

        $this->actingAs($student)->get(route('learning-tools.mistakes.index'))->assertOk()->assertSee($question->content);
        $this->actingAs($student)->get(route('learning-tools.gaps.index'))->assertOk()->assertSee('Motion')->assertSee('0%');
        $this->actingAs($student)->patch(route('learning-tools.mistakes.update'), [
            'source_type' => 'practice', 'source_answer_id' => $answer->id, 'note' => 'Review formula', 'is_resolved' => 1,
        ])->assertRedirect();
        $this->assertDatabaseHas('learning_mistake_reviews', ['user_id' => $student->id, 'source_answer_id' => $answer->id, 'is_resolved' => true]);
    }

    public function test_student_cannot_update_another_students_mistake(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $outsider = User::factory()->create(['role' => 'student']);
        $question = Question::factory()->create();
        $attempt = PracticeAttempt::create(['student_id' => $owner->id, 'mode' => 'mixed', 'total_questions' => 1, 'correct_answers' => 0, 'started_at' => now()]);
        $answer = PracticeAnswer::create(['attempt_id' => $attempt->id, 'question_id' => $question->id, 'is_correct' => false, 'points' => 0]);

        $this->actingAs($outsider)->patch(route('learning-tools.mistakes.update'), [
            'source_type' => 'practice', 'source_answer_id' => $answer->id, 'note' => 'Tampered',
        ])->assertForbidden();
    }

    private function classroom(User $teacher): Classroom
    {
        return Classroom::create([
            'teacher_id' => $teacher->id, 'name' => 'Phase 3 classroom', 'code' => 'P3-'.str()->random(8),
            'slug' => str()->uuid()->toString(), 'school_year' => '2026-2027', 'status' => 'active',
        ]);
    }
}
