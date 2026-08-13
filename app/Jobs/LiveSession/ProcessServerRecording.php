<?php

namespace App\Jobs\LiveSession;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;
use Mindigo\TeacherLiveSession\Services\LiveServerRecordingProcessor;

final class ProcessServerRecording implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public int $timeout = 3600;

    public function __construct(public readonly int $recordingId) {}

    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public function handle(LiveServerRecordingProcessor $processor): void
    {
        $recording = LiveSessionRecording::query()->find($this->recordingId);
        if ($recording && $recording->status === 'processing') {
            $processor->process($recording);
        }
    }

    public function failed(\Throwable $exception): void
    {
        LiveSessionRecording::query()->whereKey($this->recordingId)->update([
            'status' => 'failed', 'failure_reason' => mb_substr($exception->getMessage(), 0, 1000), 'ended_at' => now(),
        ]);
    }
}
