<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\ExamManagement\Services\ExamInventoryService;
use Tests\TestCase;

class ExamFoundationPhaseZeroOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_inventory_command_reports_existing_schema(): void
    {
        $report = app(ExamInventoryService::class)->collect();

        $this->assertSame('exam', $report['schema']);
        $this->assertSame(0, $report['tables']['exam_attempts']);
        $this->assertSame(0, $report['orphaned_attempts']);

        $this->artisan('exam:inventory', ['--json' => true])->assertSuccessful();
    }

    public function test_teacher_can_access_the_exam_foundation(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('teacher.exams.foundation'))
            ->assertOk()
            ->assertSee(__('Mindigo-exam-management::app.foundation.foundation_title'))
            ->assertDontSee('reviewing', false);
    }

    public function test_admin_and_student_cannot_access_the_teaching_foundation(): void
    {
        $this->actingAs($this->createUser(['role' => 'admin']))
            ->get(route('teacher.exams.foundation'))
            ->assertRedirect();

        $this->actingAs($this->createUser(['role' => 'student']))
            ->get(route('teacher.exams.foundation'))
            ->assertRedirect();
    }

    public function test_admin_cannot_enter_exam_business_routes(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($admin)->get(route('teacher.exams.index'))->assertRedirect();
        $this->actingAs($admin)->get(route('exams.index'))->assertForbidden();
    }
}
