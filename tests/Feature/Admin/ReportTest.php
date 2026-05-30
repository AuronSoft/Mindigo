<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacher;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin   = User::factory()->create(['role' => 'admin',   'is_active' => true]);
        $this->teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
        $this->student = User::factory()->create(['role' => 'student', 'is_active' => true]);
    }

    // Access control
    public function test_guest_cannot_access_reports(): void
    {
        $this->get('/reports')->assertRedirect('/login');
    }

    public function test_student_cannot_access_reports(): void
    {
        $this->actingAs($this->student)->get('/reports')->assertForbidden();
    }

    public function test_teacher_cannot_access_reports(): void
    {
        $this->actingAs($this->teacher)->get('/reports')->assertForbidden();
    }

    public function test_admin_can_access_report_overview(): void
    {
        $this->actingAs($this->admin)->get('/reports')->assertOk();
    }

    public function test_admin_can_access_exam_reports_list(): void
    {
        $this->actingAs($this->admin)->get('/reports/exams')->assertOk();
    }

    public function test_admin_can_access_student_reports_list(): void
    {
        $this->actingAs($this->admin)->get('/reports/students')->assertOk();
    }

    // Report overview data 
    public function test_report_overview_has_all_required_variables(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports');

        $response->assertViewHasAll([
            'overview', 'trend', 'topExams', 'topStudents',
            'subjectBreakdown', 'scoreDistribution',
        ]);
    }

    public function test_report_overview_stats_reflect_real_data(): void
    {
        $exam = Exam::factory()->create(['created_by' => $this->admin->id, 'status' => 'published', 'total_points' => 10]);
        ExamAttempt::factory()->create(['exam_id' => $exam->id, 'user_id' => $this->student->id, 'status' => 'submitted', 'passed' => true,  'percentage' => 80]);
        ExamAttempt::factory()->create(['exam_id' => $exam->id, 'user_id' => $this->teacher->id, 'status' => 'submitted', 'passed' => false, 'percentage' => 30]);

        $response = $this->actingAs($this->admin)->get('/reports');
        $overview = $response->viewData('overview');

        $this->assertEquals(2, $overview['total_attempts']);
        $this->assertEquals(1, $overview['passed_attempts']);
        $this->assertEquals(50.0, $overview['pass_rate']);
    }

    public function test_score_distribution_covers_all_buckets(): void
    {
        $response     = $this->actingAs($this->admin)->get('/reports');
        $distribution = $response->viewData('scoreDistribution');

        $this->assertArrayHasKey('0–20',   $distribution);
        $this->assertArrayHasKey('20–40',  $distribution);
        $this->assertArrayHasKey('40–60',  $distribution);
        $this->assertArrayHasKey('60–80',  $distribution);
        $this->assertArrayHasKey('80–100', $distribution);
    }

    public function test_attempt_trend_has_30_days(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports');
        $trend    = $response->viewData('trend');

        $this->assertArrayHasKey('labels', $trend);
        $this->assertArrayHasKey('counts', $trend);
        $this->assertCount(30, $trend['labels']);
        $this->assertCount(30, $trend['counts']);
    }

    // Exam detail report
    public function test_admin_can_view_exam_detail_report(): void
    {
        $exam = Exam::factory()->create(['created_by' => $this->admin->id, 'total_points' => 10]);

        $this->actingAs($this->admin)
            ->get("/reports/exams/{$exam->id}")
            ->assertOk()
            ->assertViewIs('Mindigo-report::exam-detail');
    }

    public function test_exam_detail_report_calculates_pass_rate_correctly(): void
    {
        $exam = Exam::factory()->create(['created_by' => $this->admin->id, 'total_points' => 10]);
        $users = User::factory()->count(4)->create(['role' => 'student']);
        ExamAttempt::factory()->count(3)->create(['exam_id' => $exam->id, 'user_id' => $users[0]->id, 'status' => 'submitted', 'passed' => true,  'percentage' => 80]);
        ExamAttempt::factory()->count(1)->create(['exam_id' => $exam->id, 'user_id' => $users[3]->id, 'status' => 'submitted', 'passed' => false, 'percentage' => 30]);

        $response = $this->actingAs($this->admin)->get("/reports/exams/{$exam->id}");
        $report   = $response->viewData('report');

        $this->assertEquals(4,    $report['total']);
        $this->assertEquals(3,    $report['passed']);
        $this->assertEquals(75.0, $report['pass_rate']);
    }

    public function test_exam_detail_report_excludes_in_progress_attempts(): void
    {
        $exam = Exam::factory()->create(['created_by' => $this->admin->id, 'total_points' => 10]);
        $s2 = User::factory()->create(['role' => 'student']);
        ExamAttempt::factory()->create(['exam_id' => $exam->id, 'user_id' => $this->student->id, 'status' => 'submitted',  'passed' => true,  'percentage' => 80]);
        ExamAttempt::factory()->create(['exam_id' => $exam->id, 'user_id' => $s2->id,            'status' => 'in_progress','passed' => false, 'percentage' => 0]);

        $report = $this->actingAs($this->admin)
            ->get("/reports/exams/{$exam->id}")
            ->viewData('report');

        $this->assertEquals(1, $report['total']); // only submitted counts
    }

    public function test_exam_detail_404_for_nonexistent_exam(): void
    {
        $this->actingAs($this->admin)
            ->get('/reports/exams/99999')
            ->assertNotFound();
    }

    // Student detail report
    public function test_admin_can_view_student_detail_report(): void
    {
        $this->actingAs($this->admin)
            ->get("/reports/students/{$this->student->id}")
            ->assertOk()
            ->assertViewIs('Mindigo-report::student-detail');
    }

    public function test_student_detail_shows_correct_attempt_count(): void
    {
        $exam = Exam::factory()->create(['created_by' => $this->admin->id, 'total_points' => 10]);
        ExamAttempt::factory()->count(4)->create(['exam_id' => $exam->id, 'user_id' => $this->student->id, 'status' => 'submitted', 'percentage' => 70]);

        $report = $this->actingAs($this->admin)
            ->get("/reports/students/{$this->student->id}")
            ->viewData('report');

        $this->assertEquals(4, $report['total_attempts']);
    }

    public function test_student_detail_404_for_admin_user(): void
    {
        $this->actingAs($this->admin)
            ->get("/reports/students/{$this->admin->id}")
            ->assertNotFound();
    }

    public function test_student_detail_404_for_nonexistent_user(): void
    {
        $this->actingAs($this->admin)
            ->get('/reports/students/99999')
            ->assertNotFound();
    }

    // Pagination
    public function test_exam_reports_list_is_paginated(): void
    {
        Exam::factory()->count(25)->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get('/reports/exams');

        $response->assertOk();
        $exams = $response->viewData('exams');
        $this->assertEquals(20, $exams->perPage());
    }

    public function test_student_reports_list_is_paginated(): void
    {
        User::factory()->count(25)->create(['role' => 'student']);

        $response = $this->actingAs($this->admin)->get('/reports/students');

        $response->assertOk();
        $students = $response->viewData('students');
        $this->assertEquals(20, $students->perPage());
    }
}
