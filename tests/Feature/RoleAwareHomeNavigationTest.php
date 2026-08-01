<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Tests\TestCase;

class RoleAwareHomeNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_dashboard_links_target_the_authenticated_users_home(): void
    {
        foreach ($this->roleHomes() as $role => $home) {
            /** @var User $user */
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('profile.index'))
                ->assertOk()
                ->assertSee('href="'.$home.'"', false);
        }
    }

    public function test_exam_tips_account_link_targets_the_authenticated_users_home(): void
    {
        foreach ($this->roleHomes() as $role => $home) {
            /** @var User $user */
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('exam-tips'))
                ->assertOk()
                ->assertSee('href="'.$home.'" data-exam-tip-user-menu', false);
        }
    }

    private function roleHomes(): array
    {
        return [
            'student' => '/student',
            'teacher' => '/teacher',
            'admin' => '/dashboard',
        ];
    }
}
