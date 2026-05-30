<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Tests\TestCase;

/**
 * Verifies that every admin-only route enforces role-based access.
 * Guests → 302 to /login, students → 403, admins → 200.
 */
class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin   = User::factory()->create(['role' => 'admin',   'is_active' => true]);
        $this->student = User::factory()->create(['role' => 'student', 'is_active' => true]);
    }

    /**
     * Routes that only admins can visit.
     * Format: [path, route_name]
     */
    private function adminOnlyRoutes(): array
    {
        return [
            ['/dashboard',          'dashboard'],
            ['/dashboard/users',    'users.index'],
            ['/dashboard/audit-logs', 'audit-logs.index'],
            ['/dashboard/role-permissions', 'role-permissions.index'],
            ['/dashboard/system-settings',  'system-settings.index'],
            ['/reports',            'reports.index'],
            ['/reports/exams',      'reports.exams'],
            ['/reports/students',   'reports.students'],
        ];
    }

    /** @dataProvider adminOnlyRoutesProvider */
    public function test_guest_redirected_to_login(string $path): void
    {
        $this->get($path)->assertRedirect('/login');
    }

    /** @dataProvider adminOnlyRoutesProvider */
    public function test_student_gets_403(string $path): void
    {
        $this->actingAs($this->student)->get($path)->assertForbidden();
    }

    /** @dataProvider adminOnlyRoutesProvider */
    public function test_admin_gets_200(string $path): void
    {
        $this->actingAs($this->admin)->get($path)->assertOk();
    }

    public static function adminOnlyRoutesProvider(): array
    {
        return [
            'dashboard'         => ['/dashboard'],
            'users'             => ['/dashboard/users'],
            'audit-logs'        => ['/dashboard/audit-logs'],
            'role-permissions'  => ['/dashboard/role-permissions'],
            'system-settings'   => ['/dashboard/system-settings'],
            'reports'           => ['/reports'],
            'reports/exams'     => ['/reports/exams'],
            'reports/students'  => ['/reports/students'],
        ];
    }

    // Inactive user
    // AdminMiddleware only checks role, not is_active. Inactive admin still passes.
    public function test_inactive_admin_still_passes_role_check(): void
    {
        /** @var User $inactive */
        $inactive = User::factory()->create(['role' => 'admin', 'is_active' => false]);

        $this->actingAs($inactive)
            ->get('/dashboard')
            ->assertOk();
    }

    // Question-bank permission 
    public function test_admin_can_access_question_bank(): void
    {
        $this->actingAs($this->admin)
            ->get('/dashboard/question-bank')
            ->assertOk();
    }

    public function test_admin_can_access_exam_list(): void
    {
        $this->actingAs($this->admin)
            ->get('/dashboard/exams')
            ->assertOk();
    }
}
