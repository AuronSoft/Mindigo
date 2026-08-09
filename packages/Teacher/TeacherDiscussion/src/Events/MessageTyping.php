<?php

namespace Mindigo\TeacherDiscussion\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherDiscussion\Models\DiscussionThread;

class MessageTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DiscussionThread $thread, public User $user) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('discussion.'.$this->thread->id)];
    }

    public function broadcastAs(): string
    {
        return 'message.typing';
    }

    public function broadcastWith(): array
    {
        return [
            'thread_id' => $this->thread->id,
            'user_id' => $this->user->getAuthIdentifier(),
            'name' => $this->user->name,
        ];
    }
}
