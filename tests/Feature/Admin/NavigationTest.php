<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Tests\TestCase;

/**
 * Verifies that key navigation links in the sidebar/dashboard
 * point to the correct URLs and return 200.
 */
class NavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    // Sidebar links 
    public function test_sidebar_dashboard_link_works(): void
    {
        $this->actingAs($this->admin)->get('/dashboard')->assertOk();
    }

    public function test_sidebar_question_bank_link_works(): void
    {
        $this->actingAs($this->admin)->get('/dashboard/question-bank')->assertOk();
    }

    public function test_sidebar_exams_link_works(): void
    {
        $this->actingAs($this->admin)->get('/dashboard/exams')->assertOk();
    }

    public function test_sidebar_users_link_works(): void
    {
        $this->actingAs($this->admin)->get('/dashboard/users')->assertOk();
    }

    public function test_sidebar_audit_logs_link_works(): void
    {
        $this->actingAs($this->admin)->get('/dashboard/audit-logs')->assertOk();
    }

    public function test_sidebar_support_tickets_link_works(): void
    {
        $this->actingAs($this->admin)->get('/dashboard/support-tickets')->assertOk();
    }

    public function test_sidebar_system_settings_link_works(): void
    {
        $this->actingAs($this->admin)->get('/dashboard/system-settings')->assertOk();
    }

    public function test_sidebar_role_permissions_link_works(): void
    {
        $this->actingAs($this->admin)->get('/dashboard/role-permissions')->assertOk();
    }

    public function test_sidebar_reports_link_works(): void
    {
        $this->actingAs($this->admin)->get('/reports')->assertOk();
    }

    // Dashboard CTA buttons 
    public function test_chi_tiet_button_route_exists(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('reports.index'));
    }

    public function test_xem_bao_cao_button_route_exists(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('reports.exams'));
    }

    public function test_chi_tiet_button_url_leads_to_200(): void
    {
        $url = route('reports.index');
        $this->actingAs($this->admin)->get($url)->assertOk();
    }

    public function test_xem_bao_cao_button_url_leads_to_200(): void
    {
        $url = route('reports.exams');
        $this->actingAs($this->admin)->get($url)->assertOk();
    }

    // Report sub-navigation 

    public function test_report_overview_back_to_dashboard_route_exists(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('dashboard'));
    }

    public function test_report_exam_list_link_leads_to_200(): void
    {
        $this->actingAs($this->admin)->get(route('reports.exams'))->assertOk();
    }

    public function test_report_student_list_link_leads_to_200(): void
    {
        $this->actingAs($this->admin)->get(route('reports.students'))->assertOk();
    }

    // Language switcher 
    public function test_switch_to_english_redirects_back(): void
    {
        $this->from('/dashboard')
            ->get('/lang/en')
            ->assertRedirect('/dashboard')
            ->assertSessionHas('locale', 'en');
    }

    public function test_switch_to_vietnamese_redirects_back(): void
    {
        $this->from('/dashboard')
            ->get('/lang/vi')
            ->assertRedirect('/dashboard')
            ->assertSessionHas('locale', 'vi');
    }

    // Profile link 
    public function test_profile_page_accessible_by_admin(): void
    {
        $this->actingAs($this->admin)->get('/profile')->assertOk();
    }
}
