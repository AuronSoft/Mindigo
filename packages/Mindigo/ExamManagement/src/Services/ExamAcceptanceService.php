<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mindigo\ExamManagement\Models\ExamMigrationRun;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;

class ExamAcceptanceService
{
    public function __construct(
        private readonly ExamCutoverService $cutover,
        private readonly LegacyExamMigrationService $migration,
    ) {}

    public function report(): array
    {
        $comparison = $this->migration->compare();
        $schema = collect(['exam_templates', 'exam_sessions', 'exam_candidates', 'exam_session_attempts', 'exam_session_attempt_answers', 'exam_proctor_events', 'exam_migration_runs'])
            ->mapWithKeys(fn (string $table): array => [$table => Schema::hasTable($table)])->all();
        $routes = collect(['teacher.exam-templates.index', 'teacher.exam-sessions.index', 'teacher.exam-sessions.monitoring.index', 'teacher.exam-sessions.grading.index', 'teacher.exam-sessions.analytics.show', 'student.exam-sessions.index', 'admin.exam-operations', 'admin.exam-cutover.index'])
            ->mapWithKeys(fn (string $route): array => [$route => Route::has($route)])->all();
        $latestRun = ExamMigrationRun::query()->latest()->first();
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
        $staleAttempts = ExamSessionAttempt::query()->whereIn('status', [ExamSessionAttempt::STATUS_IN_PROGRESS, ExamSessionAttempt::STATUS_PAUSED])
            ->where('last_activity_at', '<', now()->subMinutes(5))->count();
        $checks = [
            'schema' => ! in_array(false, $schema, true),
            'routes' => ! in_array(false, $routes, true),
            'reconciliation' => collect($comparison)->every(fn (array $row): bool => $row['matches']),
            'migration_run' => ! $latestRun || $latestRun->status === 'completed',
            'failed_jobs' => $failedJobs === 0,
        ];

        return [
            'ready' => ! in_array(false, $checks, true),
            'mode' => $this->cutover->mode(),
            'checks' => $checks,
            'schema' => $schema,
            'routes' => $routes,
            'comparison' => $comparison,
            'operations' => ['failed_jobs' => $failedJobs, 'stale_attempts' => $staleAttempts, 'broadcast_driver' => config('broadcasting.default'), 'queue_connection' => config('queue.default')],
            'latest_run' => $latestRun?->only(['uuid', 'status', 'started_at', 'completed_at', 'summary', 'issues']),
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
