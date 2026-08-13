<?php

namespace App\Jobs\LiveSession;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mindigo\TeacherLiveSession\Models\LiveProviderWebhookEvent;
use Mindigo\TeacherLiveSession\Services\LiveProviderWebhookProcessor;

final class ProcessLiveProviderWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public readonly int $eventId) {}

    public function backoff(): array
    {
        return [10, 30, 120, 300];
    }

    public function handle(LiveProviderWebhookProcessor $processor): void
    {
        $event = LiveProviderWebhookEvent::query()->find($this->eventId);
        if ($event && $event->processed_at === null) {
            $processor->process($event);
        }
    }
}
