<?php

namespace Mindigo\TeacherDiscussion\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mindigo\TeacherDiscussion\Models\DiscussionMessage;

class MessageUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DiscussionMessage $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('discussion.'.$this->message->thread_id)];
    }

    public function broadcastAs(): string
    {
        return 'message.updated';
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'thread_id' => $this->message->thread_id,
            'body' => $this->message->body,
            'edited_at' => $this->message->edited_at?->toIso8601String(),
        ];
    }
}
