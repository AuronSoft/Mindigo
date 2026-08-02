<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\LearningTools\Models\Flashcard;
use Mindigo\LearningTools\Models\FlashcardDeck;
use Mindigo\LearningTools\Models\StudyPlan;
use Mindigo\LearningTools\Models\StudyPlanTask;
use Tests\TestCase;

class LearningToolsPhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_deck_add_card_and_save_review(): void
    {
        $student = $this->createUser(['role' => 'student']);

        $this->actingAs($student)->post(route('learning-tools.flashcards.store'), [
            'title' => 'Biology deck',
            'visibility' => 'private',
        ])->assertRedirect();

        $deck = FlashcardDeck::where('owner_id', $student->id)->sole();
        $this->actingAs($student)->post(route('learning-tools.flashcards.cards.store', $deck), [
            'front' => 'What is DNA?',
            'back' => 'Genetic material.',
        ])->assertRedirect();

        $card = Flashcard::where('flashcard_deck_id', $deck->id)->sole();
        $this->actingAs($student)->post(route('learning-tools.flashcards.review', [$deck, $card]), [
            'rating' => 'good',
        ])->assertRedirect();

        $this->assertDatabaseHas('flashcard_progress', [
            'user_id' => $student->id,
            'flashcard_id' => $card->id,
            'rating' => 'good',
            'interval_days' => 3,
        ]);
    }

    public function test_teacher_can_assign_deck_to_own_classroom(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher);
        $classroom->students()->attach($student->id);

        $this->actingAs($teacher)->post(route('learning-tools.flashcards.store'), [
            'title' => 'Assigned deck',
            'visibility' => 'private',
            'classroom_ids' => [$classroom->id],
        ])->assertRedirect();

        $deck = FlashcardDeck::where('owner_id', $teacher->id)->sole();

        $this->actingAs($student)
            ->get(route('learning-tools.flashcards.show', $deck))
            ->assertOk()
            ->assertSee('Assigned deck');
    }

    public function test_student_can_manage_personal_plan_and_complete_task(): void
    {
        $student = $this->createUser(['role' => 'student']);

        $this->actingAs($student)->post(route('learning-tools.plans.store'), [
            'title' => 'Exam plan',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ])->assertRedirect();

        $plan = StudyPlan::where('creator_id', $student->id)->sole();
        $this->actingAs($student)->post(route('learning-tools.plans.tasks.store', $plan), [
            'title' => 'Review chapter one',
            'due_date' => now()->addDay()->toDateString(),
        ])->assertRedirect();

        $task = StudyPlanTask::where('study_plan_id', $plan->id)->sole();
        $this->actingAs($student)
            ->post(route('learning-tools.plans.tasks.toggle', [$plan, $task]))
            ->assertRedirect();

        $this->assertDatabaseHas('study_task_completions', [
            'study_plan_task_id' => $task->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_class_plan_is_visible_only_to_students_in_the_classroom(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $member = $this->createUser(['role' => 'student']);
        $outsider = $this->createUser(['role' => 'student']);
        $classroom = $this->classroom($teacher);
        $classroom->students()->attach($member->id);

        $plan = StudyPlan::create([
            'creator_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'title' => 'Class plan',
            'start_date' => now(),
            'end_date' => now()->addWeek(),
            'status' => 'active',
        ]);

        $this->actingAs($member)->get(route('learning-tools.plans.show', $plan))->assertOk();
        $this->actingAs($outsider)->get(route('learning-tools.plans.show', $plan))->assertForbidden();
    }

    public function test_phase_two_pages_render_for_supported_roles(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $teacher = $this->createUser(['role' => 'teacher']);

        foreach ([
            'learning-tools.flashcards.index',
            'learning-tools.flashcards.create',
            'learning-tools.plans.index',
            'learning-tools.plans.create',
        ] as $route) {
            $this->actingAs($student)->get(route($route))->assertOk();
            $this->actingAs($teacher)->get(route($route))->assertOk();
        }
    }

    private function classroom(User $teacher): Classroom
    {
        return Classroom::create([
            'teacher_id' => $teacher->id,
            'name' => 'Test classroom',
            'code' => 'CLASS-'.str()->random(8),
            'slug' => str()->uuid()->toString(),
            'school_year' => '2026-2027',
            'status' => 'active',
        ]);
    }
}
