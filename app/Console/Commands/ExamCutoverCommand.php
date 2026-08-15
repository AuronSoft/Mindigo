<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mindigo\ExamManagement\Services\ExamCutoverService;

class ExamCutoverCommand extends Command
{
    protected $signature = 'exam:cutover {mode? : parallel or new} {--beta=* : Teacher IDs routed to the new module while parallel} {--status : Show current cutover configuration}';

    protected $description = 'Inspect or change the controlled exam module cutover mode';

    public function handle(ExamCutoverService $cutover): int
    {
        if ($this->option('status') || ! $this->argument('mode')) {
            $this->components->twoColumnDetail('Mode', $cutover->mode());
            $this->components->twoColumnDetail('Beta teachers', implode(', ', $cutover->betaTeacherIds()) ?: 'none');

            return self::SUCCESS;
        }

        $mode = (string) $this->argument('mode');
        if (! in_array($mode, ExamCutoverService::MODES, true)) {
            $this->error('Mode must be parallel or new.');

            return self::INVALID;
        }
        $cutover->configure($mode, $this->option('beta'));
        $this->info("Exam cutover mode changed to {$mode}.");

        return self::SUCCESS;
    }
}
