<?php

namespace App\Console\Commands\LiveSession;

use Illuminate\Console\Command;
use Mindigo\TeacherLiveSession\Services\LiveProviderSyncService;

final class SyncLiveProvidersCommand extends Command
{
    protected $signature = 'live-sessions:sync-providers {--limit=100 : Maximum sessions per run}';

    protected $description = 'Synchronize active Google Meet and Zoom sessions';

    public function handle(LiveProviderSyncService $sync): int
    {
        $limit = max(1, min(500, (int) $this->option('limit')));
        $stats = $sync->syncDue($limit);
        $this->info("Provider sync queued: {$stats['queued']} sessions.");

        return self::SUCCESS;
    }
}
