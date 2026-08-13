<?php

namespace App\Console\Commands\LiveSession;

use Illuminate\Console\Command;
use Mindigo\TeacherLiveSession\Services\LiveSessionDisasterRecoveryService;

final class RestoreLiveSessionDisasterRecoveryCommand extends Command
{
    protected $signature = 'live-sessions:dr-restore {archive} {--disk= : Override the configured off-site disk} {--apply : Restore missing database rows and media files} {--force : Confirm destructive production restore operation}';

    protected $description = 'Verify a disaster-recovery archive or explicitly restore it';

    public function handle(LiveSessionDisasterRecoveryService $recovery): int
    {
        $path = (string) $this->argument('archive');
        $disk = $this->option('disk') ?: null;
        $inspection = $recovery->drill($path, $disk);
        $this->info("Archive verified: {$inspection['tables']} tables, {$inspection['records']} records, {$inspection['files']} files.");
        if (! $this->option('apply')) {
            $this->warn('Restore drill only. No database or media data was changed.');

            return self::SUCCESS;
        }
        if (! $this->option('force')) {
            $this->error('Use both --apply and --force after validating the restore target and maintenance window.');

            return self::FAILURE;
        }
        $result = $recovery->restore($path, $disk);
        $this->info("Restore completed: {$result['records']} records and {$result['files']} files inspected/restored.");

        return self::SUCCESS;
    }
}
