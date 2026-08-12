<?php

namespace App\Jobs\LiveSession;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Services\LiveProviderSyncService;

final class SyncLiveProviderSession implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $liveSessionId) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function uniqueId(): string
    {
        return (string) $this->liveSessionId;
    }

    public function handle(LiveProviderSyncService $sync): void
    {
        $session = LiveSession::query()->find($this->liveSessionId);
        if ($session !== null) {
            $sync->sync($session);
        }
    }
}
