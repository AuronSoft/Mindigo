<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Mindigo\ExamManagement\Services\ExamCutoverService;
use Mindigo\StudentDashboard\Services\DashboardService as StudentDashboardService;
use Mindigo\StudentLeaderboard\Services\LeaderboardService;
use Mindigo\StudentProgress\Services\ProgressService;
use Mindigo\TeacherDashboard\Services\TeacherDashboardService;
use Tests\TestCase;

class ExamPlatformConvergencePhaseFourteenTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_and_search_use_new_exam_domain_after_cutover(): void
    {
        [$teacher, , $session] = $this->fixture();
        $admin = $this->createUser(['role' => 'admin']);
        app(ExamCutoverService::class)->configure(ExamCutoverService::MODE_NEW);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('totalExams', 1)
            ->assertViewHas('totalAttempts', 1);
        $this->actingAs($admin)->getJson(route('dashboard.search', ['q' => 'Convergence']))
            ->assertOk()
            ->assertJsonPath('results.0.label', $session->title)
            ->assertJsonPath('results.0.url', route('admin.exam-operations'));
        $this->actingAs($admin)->get(route('reports.index'))->assertRedirect(route('admin.exam-operations'));
        $this->actingAs($admin)->get(route('reports.students'))->assertRedirect(route('admin.exam-operations'));
        $this->actingAs($teacher)->get(route('teacher.results.index'))->assertRedirect(route('teacher.exam-sessions.index'));
    }

    public function test_student_progress_and_leaderboard_use_session_attempts(): void
    {
        [, $student] = $this->fixture();
        app(ExamCutoverService::class)->configure(ExamCutoverService::MODE_NEW);

        $progress = app(ProgressService::class)->computeForStudent($student);
        $ranking = app(LeaderboardService::class)->ranking($student);

        $this->assertSame(['done' => 1, 'total' => 1, 'rate' => 100], $progress['exam']);
        $this->assertSame(80, $progress['avg_score']);
        $this->assertSame(80.0, $ranking['me']->score);
        $this->assertSame(1, $ranking['me']->completed);
    }

    public function test_role_dashboards_use_the_same_converged_exam_source(): void
    {
        [$teacher, $student] = $this->fixture();
        app(ExamCutoverService::class)->configure(ExamCutoverService::MODE_NEW);

        $teacherStats = app(TeacherDashboardService::class)->getStats($teacher);
        $studentStats = app(StudentDashboardService::class)->getStudyStats($student, collect());

        $this->assertSame(1, $teacherStats['totalExams']);
        $this->assertSame(1, $teacherStats['totalAttempts']);
        $this->assertSame(1, $teacherStats['passedAttempts']);
        $this->assertSame(1, $studentStats['exams']['total']);
        $this->assertSame(1, $studentStats['exams']['done']);
        $this->assertSame(80, $studentStats['avg_score']['percent']);
    }

    public function test_cutover_workspace_is_admin_only_and_requires_explicit_confirmation(): void
    {
        [$teacher] = $this->fixture();
        $admin = $this->createUser(['role' => 'admin']);

        $this->actingAs($teacher)->get(route('admin.exam-cutover.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.exam-cutover.index'))
            ->assertOk()->assertSee(__('Mindigo-exam-management::app.cutover.title'));
        $this->actingAs($admin)->put(route('admin.exam-cutover.update'), ['mode' => 'new', 'confirmation' => 'wrong'])->assertSessionHasErrors('confirmation');
        $this->actingAs($admin)->put(route('admin.exam-cutover.update'), ['mode' => 'new', 'confirmation' => 'CUTOVER'])->assertRedirect();
        $this->assertSame(ExamCutoverService::MODE_NEW, app(ExamCutoverService::class)->mode());
    }

    public function test_production_acceptance_command_checks_converged_platform_contracts(): void
    {
        $this->fixture();

        $this->artisan('exam:acceptance', ['--json' => true])
            ->expectsOutputToContain('"ready": true')
            ->assertSuccessful();
    }

    private function fixture(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $template = ExamTemplate::query()->create(['owner_id' => $teacher->id, 'title' => 'Convergence template', 'slug' => 'convergence-template-'.str()->random(6), 'status' => ExamTemplate::STATUS_READY, 'ready_at' => now()]);
        $version = ExamTemplateVersion::query()->create(['exam_template_id' => $template->id, 'created_by' => $teacher->id, 'version' => 1, 'title' => $template->title, 'total_questions' => 1, 'total_points' => 10, 'locked_at' => now()]);
        $session = ExamSession::query()->create(['exam_template_version_id' => $version->id, 'organizer_id' => $teacher->id, 'title' => 'Convergence final exam', 'slug' => 'convergence-session-'.str()->random(6), 'status' => ExamSession::STATUS_COMPLETED, 'starts_at' => now()->subHours(2), 'ends_at' => now()->subHour(), 'duration_minutes' => 60, 'max_attempts' => 1, 'passing_score' => 5]);
        $candidate = ExamCandidate::query()->create(['exam_session_id' => $session->id, 'user_id' => $student->id, 'name' => $student->name, 'email' => $student->email]);
        ExamSessionAttempt::query()->create(['exam_session_id' => $session->id, 'exam_candidate_id' => $candidate->id, 'user_id' => $student->id, 'attempt_number' => 1, 'status' => ExamSessionAttempt::STATUS_SUBMITTED, 'started_at' => now()->subHour(), 'expires_at' => now(), 'last_activity_at' => now(), 'submitted_at' => now(), 'question_order' => [], 'score' => 8, 'max_score' => 10, 'percentage' => 80, 'passed' => true]);

        return [$teacher, $student, $session];
    }
}
