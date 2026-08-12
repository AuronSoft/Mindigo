<?php

namespace App\Console\Commands\LiveSession;

use Illuminate\Console\Command;
use Mindigo\TeacherLiveSession\Services\LiveSessionArchiveService;

final class BackupLiveSessionDataCommand extends Command
{
    protected $signature = 'live-sessions:backup {--disk=local}';

    protected $description = 'Create a private redacted archive of durable live-classroom data';

    public function handle(LiveSessionArchiveService $archives): int
    {
        $result = $archives->backup((string) $this->option('disk'));
        $this->table(['Archive', 'Records', 'SHA-256'], [[$result['path'], $result['records'], $result['checksum']]]);

        return self::SUCCESS;
    }
}
