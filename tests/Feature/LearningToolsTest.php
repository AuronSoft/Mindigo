<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('learning-tools.index'))->assertRedirect(route('login'));
    }

    public function test_student_can_view_learning_tools(): void
    {
        $student = $this->createUser(['role' => 'student']);

        $this->actingAs($student)->get(route('learning-tools.index'))
            ->assertOk()
            ->assertViewIs('learning-tools::index')
            ->assertSee(__('learning-tools::app.title'));
    }

    public function test_teacher_can_view_learning_tools(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('learning-tools.index'))
            ->assertOk()
            ->assertSee(__('learning-tools::app.tools.flashcards.name'));
    }

    public function test_tools_can_be_filtered_and_searched(): void
    {
        $student = $this->createUser(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('learning-tools.index', ['category' => 'orientation']))
            ->assertSee(__('learning-tools::app.tools.score_calculator.name'))
            ->assertDontSee(__('learning-tools::app.tools.pomodoro.name'));

        $this->actingAs($student)
            ->get(route('learning-tools.index', ['q' => __('learning-tools::app.tools.flashcards.name')]))
            ->assertSee(__('learning-tools::app.tools.flashcards.name'))
            ->assertDontSee(__('learning-tools::app.tools.pomodoro.name'));
    }
}
