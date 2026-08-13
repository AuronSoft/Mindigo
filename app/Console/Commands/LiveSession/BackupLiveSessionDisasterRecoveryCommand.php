<?php

namespace App\Console\Commands\LiveSession;

use Illuminate\Console\Command;
use Mindigo\TeacherLiveSession\Services\LiveSessionDisasterRecoveryService;

final class BackupLiveSessionDisasterRecoveryCommand extends Command
{
    protected $signature = 'live-sessions:dr-backup {--disk= : Override the configured off-site disk}';

    protected $description = 'Create an encrypted full database and live-media disaster-recovery archive';

    public function handle(LiveSessionDisasterRecoveryService $recovery): int
    {
        $result = $recovery->backup($this->option('disk') ?: null);
        $this->table(['Disk', 'Archive', 'Verified', 'Tables', 'Records', 'Files', 'Bytes', 'SHA-256'], [[
            $result['disk'], $result['path'], $result['verified'] ? 'yes' : 'no', $result['tables'], $result['records'], $result['files'], $result['size'], $result['sha256'],
        ]]);

        return self::SUCCESS;
    }
}
