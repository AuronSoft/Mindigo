<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mindigo\ExamManagement\Services\ExamAcceptanceService;

class ExamAcceptanceCommand extends Command
{
    protected $signature = 'exam:acceptance {--json : Print machine-readable JSON}';

    protected $description = 'Run production acceptance checks for the converged exam platform';

    public function handle(ExamAcceptanceService $acceptance): int
    {
        $report = $acceptance->report();
        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } else {
            $this->components->twoColumnDetail('Cutover mode', $report['mode']);
            foreach ($report['checks'] as $check => $passed) {
                $this->components->twoColumnDetail($check, $passed ? '<fg=green>PASS</>' : '<fg=red>FAIL</>');
            }
            $this->line('Stale attempts: '.$report['operations']['stale_attempts']);
        }

        return $report['ready'] ? self::SUCCESS : self::FAILURE;
    }
}
