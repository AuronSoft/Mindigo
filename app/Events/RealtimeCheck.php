<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RealtimeCheck implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $payload = 'phase0-ok') {}

    public function broadcastOn(): array
    {
        return [new Channel('test-realtime-check')];
    }

    public function broadcastAs(): string
    {
        return 'realtime.check';
    }
}
