<?php

namespace Mindigo\TeacherDiscussion\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mindigo\TeacherDiscussion\Models\DiscussionMessage;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DiscussionMessage $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('discussion.'.$this->message->thread_id)];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'thread_id' => $this->message->thread_id,
                'sender_id' => $this->message->sender_id,
                'body' => $this->message->body,
                'created_at' => $this->message->created_at?->toIso8601String(),
                'sender' => [
                    'name' => $this->message->sender?->name,
                ],
            ],
        ];
    }
}
