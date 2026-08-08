<?php

namespace Mindigo\TeacherDiscussion\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mindigo\TeacherDiscussion\Models\DiscussionMessage;

class MessageReacted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DiscussionMessage $message,
        public string $emoji,
        public int $userId
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('discussion.'.$this->message->thread_id)];
    }

    public function broadcastAs(): string
    {
        return 'message.reacted';
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'thread_id' => $this->message->thread_id,
            'emoji' => $this->emoji,
            'user_id' => $this->userId,
            'reactions' => $this->message->reactions->groupBy('emoji')
                ->map(fn ($items, $emoji) => [
                    'emoji' => $emoji,
                    'count' => $items->count(),
                    'users' => $items->pluck('user.name')->values()->all(),
                ])
                ->values()
                ->all(),
        ];
    }
}
