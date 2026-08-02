<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\QuestionBank\Models\Question;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin',   'is_active' => true]);
        $this->teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
        $this->student = User::factory()->create(['role' => 'student', 'is_active' => true]);
    }

    // Access control
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_student_cannot_access_dashboard(): void
    {
        $this->actingAs($this->student)
            ->get('/dashboard')
            ->assertForbidden();
    }

    public function test_teacher_cannot_access_admin_dashboard(): void
    {
        // Dashboard uses AdminMiddleware — only admins allowed
        $this->actingAs($this->teacher)
            ->get('/dashboard')
            ->assertForbidden();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get('/dashboard')
            ->assertOk();
    }

    // Page renders without errors
    public function test_dashboard_returns_200_with_no_data(): void
    {
        $this->actingAs($this->admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertViewIs('Mindigo-dashboard::dashboard');
    }

    public function test_dashboard_has_correct_view_variables(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertViewHasAll([
            'stats', 'totalExams', 'totalAttempts', 'passRate',
            'topPerformer', 'bestExam', 'latestExams', 'topPerformers',
            'totalQuestions', 'pendingReview', 'pendingPublish',
            'topSubjects', 'rankingLabels', 'rankingData',
            'userMetrics', 'dashboardUser',
        ]);
    }

    // Real data rendering

    public function test_dashboard_shows_correct_user_counts(): void
    {
        User::factory()->count(3)->create(['role' => 'student', 'is_active' => true]);
        User::factory()->count(2)->create(['role' => 'teacher', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->get('/dashboard');
        $stats = $response->viewData('stats');

        // setUp created 1 admin, 1 teacher, 1 student; factory added 3 more students + 2 teachers
        $this->assertEquals(1, $stats['admins']);
        $this->assertEquals(3, $stats['teachers']);
        $this->assertEquals(4, $stats['students']);
    }

    public function test_dashboard_shows_exam_count(): void
    {
        Exam::factory()->count(5)->create(['created_by' => $this->admin->id, 'status' => 'published']);

        $response = $this->actingAs($this->admin)->get('/dashboard');
        $totalExams = $response->viewData('totalExams');

        $this->assertEquals(5, $totalExams);
    }

    public function test_dashboard_pass_rate_calculated_correctly(): void
    {
        $exam = Exam::factory()->create(['created_by' => $this->admin->id, 'total_points' => 10]);

        ExamAttempt::factory()->create(['exam_id' => $exam->id, 'user_id' => $this->student->id, 'status' => 'submitted', 'passed' => true,  'percentage' => 90]);
        ExamAttempt::factory()->create(['exam_id' => $exam->id, 'user_id' => $this->teacher->id, 'status' => 'submitted', 'passed' => false, 'percentage' => 40]);

        $response = $this->actingAs($this->admin)->get('/dashboard');
        $passRate = $response->viewData('passRate');

        $this->assertEquals(50, $passRate); // 1 out of 2 passed
    }

    public function test_dashboard_pending_review_count_is_real(): void
    {
        Question::factory()->count(3)->create(['status' => 'reviewing', 'created_by' => $this->teacher->id]);
        Question::factory()->count(2)->create(['status' => 'approved',  'created_by' => $this->teacher->id]);

        $response = $this->actingAs($this->admin)->get('/dashboard');
        $pendingReview = $response->viewData('pendingReview');

        $this->assertEquals(3, $pendingReview);
    }

    public function test_dashboard_latest_exams_are_ordered_newest_first(): void
    {
        $old = Exam::factory()->create(['created_by' => $this->admin->id, 'created_at' => now()->subDays(10)]);
        $new = Exam::factory()->create(['created_by' => $this->admin->id, 'created_at' => now()]);

        $response = $this->actingAs($this->admin)->get('/dashboard');
        $latestExams = $response->viewData('latestExams');

        $this->assertEquals($new->id, $latestExams->first()->id);
    }

    public function test_dashboard_top_performers_limited_to_5(): void
    {
        $students = User::factory()->count(8)->create(['role' => 'student']);
        $exam = Exam::factory()->create(['created_by' => $this->admin->id, 'total_points' => 10]);

        foreach ($students as $s) {
            ExamAttempt::factory()->create([
                'exam_id' => $exam->id, 'user_id' => $s->id,
                'status' => 'submitted', 'percentage' => rand(50, 100),
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/dashboard');
        $topPerformers = $response->viewData('topPerformers');

        $this->assertLessThanOrEqual(5, count($topPerformers));
    }

    // User metrics card
    public function test_user_metrics_contains_correct_roles(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $userMetrics = $response->viewData('userMetrics');

        $initials = array_column($userMetrics, 'initial');
        $this->assertContains('A', $initials);
        $this->assertContains('GV', $initials);
        $this->assertContains('HS', $initials);
        $this->assertContains('ON', $initials);
    }

    public function test_total_users_not_zero_to_prevent_division_by_zero(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $totalUsers = $response->viewData('totalUsers');

        $this->assertGreaterThan(0, $totalUsers);
    }

    // Ranking chart data
    public function test_ranking_labels_and_data_are_arrays(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $this->assertIsArray($response->viewData('rankingLabels'));
        $this->assertIsArray($response->viewData('rankingData'));
    }

    public function test_ranking_labels_and_data_have_same_count(): void
    {
        $exam = Exam::factory()->create(['created_by' => $this->admin->id, 'total_points' => 10]);
        ExamAttempt::factory()->count(3)->create([
            'exam_id' => $exam->id, 'status' => 'submitted', 'user_id' => $this->student->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $this->assertCount(
            count($response->viewData('rankingLabels')),
            $response->viewData('rankingData')
        );
    }
}
