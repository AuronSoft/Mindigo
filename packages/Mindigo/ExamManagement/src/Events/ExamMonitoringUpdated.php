<?php

namespace Mindigo\ExamManagement\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamMonitoringUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $sessionId,
        public readonly ?int $attemptId,
        public readonly string $reason,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("exam-session.{$this->sessionId}")];
    }

    public function broadcastAs(): string
    {
        return 'exam.monitoring.updated';
    }

    public function broadcastWith(): array
    {
        return ['attempt_id' => $this->attemptId, 'reason' => $this->reason, 'updated_at' => now()->toIso8601String()];
    }
}
