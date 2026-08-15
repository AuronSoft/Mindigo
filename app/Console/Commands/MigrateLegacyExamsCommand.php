<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mindigo\ExamManagement\Services\ExamCutoverService;
use Mindigo\ExamManagement\Services\LegacyExamMigrationService;

class MigrateLegacyExamsCommand extends Command
{
    protected $signature = 'exam:migrate-legacy
        {--exam=* : Restrict the operation to legacy exam IDs}
        {--dry-run : Inspect without writing data}
        {--compare : Compare legacy and migrated row counts}
        {--rollback : Remove migrated copies for the selected legacy exams}
        {--force : Confirm a rollback in non-interactive environments}
        {--json : Print machine-readable JSON}';

    protected $description = 'Safely migrate, verify, or roll back legacy exams into the session-based exam domain';

    public function handle(LegacyExamMigrationService $migration, ExamCutoverService $cutover): int
    {
        $ids = collect($this->option('exam'))->map(fn ($id): int => (int) $id)->filter()->unique()->values()->all();
        if ($this->option('rollback')) {
            if ($ids === []) {
                $this->error('Rollback requires at least one --exam ID.');

                return self::INVALID;
            }
            if (! $this->option('force') && ! $this->confirm('Remove only the migrated copies for these legacy exams?')) {
                return self::FAILURE;
            }
            if ($cutover->mode() === ExamCutoverService::MODE_NEW && ! $this->option('force')) {
                $this->error('Switch to parallel mode before rollback, or use --force for an approved emergency rollback.');

                return self::FAILURE;
            }
            $report = $migration->rollback($ids);
        } elseif ($this->option('compare')) {
            $report = $migration->compare($ids);
        } elseif ($this->option('dry-run')) {
            $report = $migration->preview($ids);
        } else {
            if ($cutover->mode() !== ExamCutoverService::MODE_PARALLEL) {
                $this->error('Legacy migration can only run in parallel mode.');

                return self::FAILURE;
            }
            $run = $migration->migrate($ids);
            $report = ['run' => $run->uuid, 'status' => $run->status, 'summary' => $run->summary, 'issues' => $run->issues];
        }

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } else {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        }

        return ($report['status'] ?? null) === 'completed_with_issues' ? self::FAILURE : self::SUCCESS;
    }
}
