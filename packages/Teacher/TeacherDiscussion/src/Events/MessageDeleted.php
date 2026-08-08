<?php

namespace Mindigo\TeacherDiscussion\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $threadId, public int $messageId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('discussion.'.$this->threadId)];
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }

    public function broadcastWith(): array
    {
        return [
            'thread_id' => $this->threadId,
            'message_id' => $this->messageId,
        ];
    }
}
