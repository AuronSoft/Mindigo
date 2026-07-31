<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\LearningTools\Models\FocusSession;
use Mindigo\LearningTools\Models\LearningNote;
use Mindigo\LearningTools\Models\LearningResource;
use Tests\TestCase;

class LearningToolsPhaseOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_start_and_complete_a_focus_session(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->post(route('learning-tools.pomodoro.store'), [
            'planned_minutes' => 25,
            'break_minutes' => 5,
        ])->assertRedirect(route('learning-tools.pomodoro.index'));

        $session = FocusSession::where('user_id', $student->id)->sole();
        $session->update(['started_at' => now()->subMinutes(5)]);

        $this->actingAs($student)
            ->patch(route('learning-tools.pomodoro.complete', $session))
            ->assertRedirect(route('learning-tools.pomodoro.index'));

        $this->assertDatabaseHas('learning_focus_sessions', [
            'id' => $session->id,
            'status' => 'completed',
            'focus_minutes' => 5,
        ]);
    }

    public function test_learning_notes_are_scoped_to_their_owner(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->post(route('learning-tools.notes.store'), [
            'title' => 'My private note',
            'content' => 'Private content',
            'is_pinned' => true,
        ])->assertRedirect();

        $note = LearningNote::where('owner_id', $student->id)->sole();

        $this->actingAs($otherStudent)
            ->get(route('learning-tools.notes.edit', $note))
            ->assertForbidden();
    }

    public function test_teacher_can_publish_resource_and_student_can_favorite_it(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($teacher)->post(route('learning-tools.resources.store'), [
            'title' => 'Important formula',
            'summary' => 'A quick revision formula.',
            'content' => 'Formula content',
            'status' => 'published',
        ])->assertRedirect();

        $resource = LearningResource::where('author_id', $teacher->id)->sole();

        $this->actingAs($student)
            ->get(route('learning-tools.resources.show', $resource))
            ->assertOk()
            ->assertSee('Important formula');

        $this->actingAs($student)
            ->post(route('learning-tools.resources.favorite', $resource))
            ->assertRedirect();

        $this->assertDatabaseHas('learning_resource_favorites', [
            'user_id' => $student->id,
            'learning_resource_id' => $resource->id,
        ]);
    }

    public function test_student_cannot_manage_learning_resources(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('learning-tools.resources.create'))
            ->assertForbidden();
    }
}
