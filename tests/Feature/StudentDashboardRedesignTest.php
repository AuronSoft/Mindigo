<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\StudentDashboard\Services\DashboardService;
use Tests\TestCase;

class StudentDashboardRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_renders_the_three_primary_regions(): void
    {
        $student = $this->createUser(['role' => 'student', 'name' => 'Mindigo Student']);

        $this->actingAs($student)->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee(__('student-dashboard::app.calendar'))
            ->assertSee(__('student-dashboard::app.my_progress'))
            ->assertSee(__('student-dashboard::app.my_profile'))
            ->assertSee(__('student-dashboard::app.planned_today'))
            ->assertSee('Mindigo Student');
    }

    public function test_month_calendar_always_contains_six_complete_weeks(): void
    {
        $calendar = app(DashboardService::class)->getMonthCalendar(collect());

        $this->assertCount(6, $calendar);
        foreach ($calendar as $week) {
            $this->assertCount(7, $week);
        }
        $this->assertCount(1, collect($calendar)->flatten(1)->where('is_today', true));
    }

    public function test_guest_is_redirected_from_student_dashboard(): void
    {
        $this->get(route('student.dashboard'))->assertRedirect();
    }
}
