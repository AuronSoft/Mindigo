<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Notifications\Notification;

class DiscussionMessageSent extends Notification
{
    public function __construct(
        public string $threadId,
        public string $sender,
        public string $preview,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'discussion',
            'icon' => 'chat-bubble-left',
            'tone' => 'green',
            'title' => $this->sender,
            'message' => $this->preview,
            'thread_id' => $this->threadId,
            'url' => $this->url,
        ];
    }
}
