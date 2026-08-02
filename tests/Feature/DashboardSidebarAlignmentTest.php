<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Tests\TestCase;

class DashboardSidebarAlignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_and_student_use_the_same_compact_sidebar_dimensions(): void
    {
        foreach (['student' => 'student.dashboard', 'teacher' => 'teacher.dashboard'] as $role => $route) {
            /** @var User $user */
            $user = $this->createUser(['role' => $role]);

            $this->actingAs($user)->get(route($route))
                ->assertOk()
                ->assertSee('data-compact-grid="grid-cols-[5rem_minmax(0,1fr)]"', false)
                ->assertSee('data-compact-width="w-20"', false)
                ->assertDontSee('data-compact-width="w-24"', false);
        }
    }
}
