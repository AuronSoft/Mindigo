<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCalendarPhaseSevenTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_calendar_uses_the_single_viewport_system_workspace(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('teacher.calendar.index', ['date' => '2026-08-12']))
            ->assertOk()
            ->assertSee('data-calendar-workspace', false)
            ->assertSee(__('teacher-calendar::app.workload'))
            ->assertSee(__('teacher-calendar::app.summary'));
    }

    public function test_student_calendar_uses_the_same_role_aware_workspace(): void
    {
        $student = $this->createUser(['role' => 'student']);

        $this->actingAs($student)->get(route('student.schedule.index', ['date' => '2026-08-12', 'view' => 'week']))
            ->assertOk()
            ->assertSee('data-student-calendar-workspace', false)
            ->assertSee(__('student-schedule::app.next_actions'));
    }

    public function test_calendar_styles_keep_mindigo_green_as_the_primary_token_without_gradients(): void
    {
        $teacherCss = file_get_contents(base_path('packages/Teacher/TeacherCalendar/src/resources/css/app.css'));
        $studentCss = file_get_contents(base_path('packages/Students/StudentSchedule/src/resources/css/app.css'));

        $this->assertStringContainsString('--calendar-primary:#16a34a', $teacherCss);
        $this->assertStringNotContainsString('linear-gradient', $teacherCss.$studentCss);
        $this->assertStringNotContainsString('radial-gradient', $teacherCss.$studentCss);
    }
}
