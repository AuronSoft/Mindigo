<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamQuestion;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Services\ExamCutoverService;
use Mindigo\ExamManagement\Services\LegacyExamMigrationService;
use Mindigo\TeacherClassroom\Models\Classroom;
use Tests\TestCase;

class ExamMigrationCutoverPhaseThirteenTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_thirteen_schema_tracks_every_legacy_mapping_and_run(): void
    {
        $this->assertTrue(Schema::hasTable('exam_migration_runs'));
        $this->assertTrue(Schema::hasColumn('exam_templates', 'legacy_exam_id'));
        $this->assertTrue(Schema::hasColumn('exam_sessions', 'legacy_exam_id'));
        $this->assertTrue(Schema::hasColumn('exam_template_questions', 'legacy_exam_question_id'));
        $this->assertTrue(Schema::hasColumn('exam_session_attempts', 'legacy_exam_attempt_id'));
        $this->assertTrue(Schema::hasColumn('exam_session_attempt_answers', 'legacy_exam_attempt_answer_id'));
    }

    public function test_dry_run_writes_nothing_and_reports_source_inventory(): void
    {
        [, , $exam] = $this->fixture();

        $this->artisan('exam:migrate-legacy', ['--exam' => [$exam->id], '--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseCount('exam_templates', 0);
        $this->assertDatabaseCount('exam_migration_runs', 0);
    }

    public function test_migration_preserves_exam_audience_attempt_answers_and_is_idempotent(): void
    {
        [$teacher, $student, $exam, $question, $legacyAttempt, $legacyAnswer, $classroom] = $this->fixture();
        $migration = app(LegacyExamMigrationService::class);

        $first = $migration->migrate([$exam->id], $teacher->id);
        $second = $migration->migrate([$exam->id], $teacher->id);

        $template = ExamTemplate::query()->where('legacy_exam_id', $exam->id)->firstOrFail();
        $session = ExamSession::query()->where('legacy_exam_id', $exam->id)->firstOrFail();
        $attempt = ExamSessionAttempt::query()->where('legacy_exam_attempt_id', $legacyAttempt->id)->firstOrFail();
        $this->assertSame('completed', $first->status);
        $this->assertSame(1, $second->summary['skipped']);
        $this->assertSame($teacher->id, $template->owner_id);
        $this->assertDatabaseHas('exam_assignments', ['exam_session_id' => $session->id, 'assignable_id' => $classroom->id]);
        $this->assertDatabaseHas('exam_candidates', ['exam_session_id' => $session->id, 'user_id' => $student->id]);
        $this->assertSame([$attempt->answers()->firstOrFail()->question->id], $attempt->question_order);
        $this->assertDatabaseHas('exam_session_attempt_answers', ['legacy_exam_attempt_answer_id' => $legacyAnswer->id, 'points_awarded' => 2]);
        $this->assertDatabaseHas('exam_template_questions', ['legacy_exam_question_id' => $question->id]);
        $this->assertDatabaseCount('exam_templates', 1);
    }

    public function test_comparison_and_scoped_rollback_are_safe(): void
    {
        [, , $exam] = $this->fixture();
        $migration = app(LegacyExamMigrationService::class);
        $migration->migrate([$exam->id]);

        $comparison = $migration->compare([$exam->id]);
        $this->assertTrue(collect($comparison)->every('matches'));
        $this->assertSame(['selected' => 1, 'removed' => 1], $migration->rollback([$exam->id]));
        $this->assertDatabaseHas('exams', ['id' => $exam->id]);
        $this->assertDatabaseMissing('exam_templates', ['legacy_exam_id' => $exam->id]);
    }

    public function test_new_mode_redirects_workspaces_and_locks_legacy_authoring(): void
    {
        [$teacher, $student, $exam] = $this->fixture();
        $admin = $this->createUser(['role' => 'admin']);
        app(ExamCutoverService::class)->configure(ExamCutoverService::MODE_NEW);

        $this->actingAs($teacher)->get(route('exams.index'))->assertRedirect(route('teacher.exam-templates.index'));
        $this->actingAs($teacher)->get(route('exams.create'))->assertStatus(423);
        $this->actingAs($teacher)->get(route('teacher.exams.index'))->assertRedirect(route('teacher.exam-sessions.index'));
        $this->actingAs($teacher)->get(route('teacher.exams.create'))->assertStatus(423);
        $this->actingAs($student)->get(route('student.exams.index'))->assertRedirect(route('student.exam-sessions.index'));
        $this->actingAs($admin)->get(route('reports.exam.detail', $exam))->assertRedirect(route('admin.exam-operations'));
    }

    public function test_beta_teacher_and_their_candidates_are_routed_to_new_workspaces(): void
    {
        [$teacher, $student, $exam] = $this->fixture();
        app(LegacyExamMigrationService::class)->migrate([$exam->id]);
        app(ExamCutoverService::class)->configure(ExamCutoverService::MODE_PARALLEL, [$teacher->id]);

        $this->actingAs($teacher)->get(route('teacher.exams.index'))->assertRedirect(route('teacher.exam-sessions.index'));
        $this->actingAs($student)->get(route('student.exams.index'))->assertRedirect(route('student.exam-sessions.index'));
    }

    private function fixture(): array
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $classroom = Classroom::query()->create([
            'created_by' => $teacher->id,
            'teacher_id' => $teacher->id,
            'type' => Classroom::TYPE_STANDALONE,
            'name' => 'Legacy exam class',
            'code' => 'LEG-'.str()->random(6),
            'slug' => 'legacy-class-'.str()->random(8),
            'status' => 'active',
        ]);
        $classroom->students()->attach($student->id, ['status' => 'active', 'joined_at' => now()]);
        $exam = Exam::query()->create([
            'created_by' => $teacher->id,
            'title' => 'Legacy final exam',
            'slug' => 'legacy-final-'.str()->random(6),
            'status' => 'published',
            'duration_minutes' => 60,
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->addHour(),
            'max_attempts' => 2,
            'passing_score' => 1,
            'show_results' => true,
            'audience' => ['classrooms' => [$classroom->id]],
            'total_questions' => 1,
            'total_points' => 2,
            'published_at' => now()->subHours(2),
        ]);
        $question = ExamQuestion::query()->create([
            'exam_id' => $exam->id,
            'sort_order' => 1,
            'type' => 'single_choice',
            'difficulty' => 'medium',
            'content' => 'Legacy question',
            'options' => [['key' => 'A', 'text' => 'Correct']],
            'correct_answers' => ['A'],
            'points' => 2,
        ]);
        $attempt = ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'submitted',
            'started_at' => now()->subHour(),
            'expires_at' => now(),
            'last_activity_at' => now(),
            'submitted_at' => now(),
            'score' => 2,
            'max_score' => 2,
            'percentage' => 100,
            'passed' => true,
            'tab_leave_count' => 2,
            'question_order' => [$question->id],
        ]);
        $answer = ExamAttemptAnswer::query()->create([
            'exam_attempt_id' => $attempt->id,
            'exam_question_id' => $question->id,
            'type' => 'single_choice',
            'answer' => ['A'],
            'is_correct' => true,
            'points_awarded' => 2,
        ]);

        return [$teacher, $student, $exam, $question, $attempt, $answer, $classroom];
    }
}
