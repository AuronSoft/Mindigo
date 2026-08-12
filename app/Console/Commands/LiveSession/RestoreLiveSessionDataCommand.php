<?php

namespace App\Console\Commands\LiveSession;

use Illuminate\Console\Command;
use Mindigo\TeacherLiveSession\Services\LiveSessionArchiveService;

final class RestoreLiveSessionDataCommand extends Command
{
    protected $signature = 'live-sessions:restore {archive} {--disk=local} {--apply : Apply the inspected archive}';

    protected $description = 'Inspect or restore a private live-classroom archive';

    public function handle(LiveSessionArchiveService $archives): int
    {
        $path = (string) $this->argument('archive');
        $disk = (string) $this->option('disk');
        $inspection = $archives->inspect($path, $disk);
        $records = collect($inspection['archive']['tables'])->sum(fn (array $rows): int => count($rows));
        $this->info("Archive valid: {$records} records, SHA-256 {$inspection['checksum']}");
        if (! $this->option('apply')) {
            $this->warn('Inspection only. Pass --apply to restore missing records.');

            return self::SUCCESS;
        }

        $this->info('Restored '.$archives->restore($path, $disk).' missing records.');

        return self::SUCCESS;
    }
}
