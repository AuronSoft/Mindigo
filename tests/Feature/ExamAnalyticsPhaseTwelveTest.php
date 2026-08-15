<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Tests\TestCase;

class ExamAnalyticsPhaseTwelveTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_twelve_schema_records_question_response_time(): void
    {
        $this->assertTrue(Schema::hasColumns('exam_session_attempt_answers', ['response_seconds', 'answered_at']));
    }

    public function test_only_session_organizer_can_view_detailed_learning_analytics(): void
    {
        [$teacher, , $session] = $this->fixture();
        $outsider = $this->createUser(['role' => 'teacher']);

        $this->actingAs($teacher)->get(route('teacher.exam-sessions.analytics.show', $session))
            ->assertOk()
            ->assertSee(__('Mindigo-exam-management::app.analytics.title'))
            ->assertSee('50.0%')
            ->assertSee('45');

        $this->actingAs($outsider)->get(route('teacher.exam-sessions.analytics.show', $session))->assertForbidden();
    }

    public function test_report_exports_question_analysis_as_csv_and_pdf(): void
    {
        [$teacher, , $session] = $this->fixture();

        $csv = $this->actingAs($teacher)->get(route('teacher.exam-sessions.analytics.export', [$session, 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Capital of France', $csv->streamedContent());

        $this->actingAs($teacher)->get(route('teacher.exam-sessions.analytics.export', [$session, 'pdf']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_sees_only_aggregated_operations_without_answer_queries(): void
    {
        [, $student] = $this->fixture();
        $admin = $this->createUser(['role' => 'admin']);
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->actingAs($admin)->get(route('admin.exam-operations'))
            ->assertOk()
            ->assertSee(__('Mindigo-exam-management::app.analytics.admin_privacy_note'))
            ->assertDontSee($student->email)
            ->assertDontSee('Paris');

        $this->assertFalse(collect($queries)->contains(fn (string $sql): bool => str_contains($sql, 'exam_session_attempt_answers')));
    }

    public function test_teacher_cannot_open_admin_operational_dashboard(): void
    {
        [$teacher] = $this->fixture();

        $this->actingAs($teacher)->get(route('admin.exam-operations'))->assertForbidden();
    }

    private function fixture(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $template = ExamTemplate::query()->create([
            'owner_id' => $teacher->id,
            'title' => 'Analytics template',
            'slug' => 'analytics-template-'.str()->lower(str()->random(6)),
            'status' => 'ready',
            'ready_at' => now(),
        ]);
        $version = ExamTemplateVersion::query()->create([
            'exam_template_id' => $template->id,
            'created_by' => $teacher->id,
            'version' => 1,
            'title' => $template->title,
            'total_questions' => 1,
            'total_points' => 10,
            'locked_at' => now(),
        ]);
        $question = ExamTemplateQuestion::query()->create([
            'exam_template_version_id' => $version->id,
            'sort_order' => 1,
            'type' => 'single_choice',
            'content' => 'Capital of France',
            'options' => [['key' => 'A', 'text' => 'Paris'], ['key' => 'B', 'text' => 'London']],
            'correct_answers' => ['A'],
            'points' => 10,
        ]);
        $session = ExamSession::query()->create([
            'exam_template_version_id' => $version->id,
            'organizer_id' => $teacher->id,
            'title' => 'Analytics session',
            'slug' => 'analytics-session-'.str()->lower(str()->random(6)),
            'status' => ExamSession::STATUS_ENDED,
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->subHour(),
            'duration_minutes' => 60,
            'max_attempts' => 1,
            'passing_score' => 5,
        ]);

        $candidate = ExamCandidate::query()->create([
            'exam_session_id' => $session->id,
            'user_id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
        ]);

        foreach ([[80, true, 'A'], [40, false, 'B']] as [$percentage, $passed, $choice]) {
            $attempt = ExamSessionAttempt::query()->create([
                'exam_session_id' => $session->id,
                'exam_candidate_id' => $candidate->id,
                'user_id' => $student->id,
                'attempt_number' => $percentage === 80 ? 1 : 2,
                'status' => ExamSessionAttempt::STATUS_SUBMITTED,
                'started_at' => now()->subHour(),
                'expires_at' => now(),
                'last_activity_at' => now(),
                'submitted_at' => now(),
                'question_order' => [$question->id],
                'score' => $percentage / 10,
                'max_score' => 10,
                'percentage' => $percentage,
                'passed' => $passed,
            ]);
            ExamSessionAttemptAnswer::query()->create([
                'exam_session_attempt_id' => $attempt->id,
                'exam_template_question_id' => $question->id,
                'type' => 'single_choice',
                'answer' => [$choice],
                'is_correct' => $choice === 'A',
                'points_awarded' => $choice === 'A' ? 10 : 0,
                'response_seconds' => $choice === 'A' ? 30 : 60,
                'answered_at' => now(),
            ]);
        }

        return [$teacher, $student, $session];
    }
}
