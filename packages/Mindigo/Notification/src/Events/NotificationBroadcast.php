<?php

namespace Mindigo\Notification\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\SerializesModels;

class NotificationBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DatabaseNotification $notification) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->notification->notifiable_id)];
    }

    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }

    public function broadcastWith(): array
    {
        $data = (array) $this->notification->data;

        return [
            'id' => $this->notification->id,
            'category' => $data['category'] ?? null,
            'title' => $data['title'] ?? null,
            'message' => $data['message'] ?? null,
            'icon' => $data['icon'] ?? 'bell',
            'tone' => $data['tone'] ?? null,
            'url' => $data['url'] ?? null,
            'teacher' => $data['teacher'] ?? null,
            'created_at' => $this->notification->created_at?->toIso8601String(),
        ];
    }
}
