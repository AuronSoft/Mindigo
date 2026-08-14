<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mindigo\ExamManagement\Services\ExamInventoryService;

class ExamInventoryCommand extends Command
{
    protected $signature = 'exam:inventory {--json : Print machine-readable JSON}';

    protected $description = 'Inspect exam module data and schema health';

    public function handle(ExamInventoryService $inventory): int
    {
        $report = $inventory->collect();

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->info('Exam module inventory');
        $this->table(['Table', 'Rows'], collect($report['tables'])->map(fn ($count, $table): array => [$table, $count ?? 'missing'])->values()->all());
        $this->table(['Exam status', 'Rows'], collect($report['exam_statuses'])->map(fn ($count, $status): array => [$status, $count])->values()->all());
        $this->line('Orphaned attempts: '.($report['orphaned_attempts'] ?? 'not checked'));

        return self::SUCCESS;
    }
}
