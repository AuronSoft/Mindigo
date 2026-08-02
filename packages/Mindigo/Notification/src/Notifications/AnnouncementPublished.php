<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Notifications\Notification;

class AnnouncementPublished extends Notification
{
    public function __construct(
        public string $title,
        public ?string $message = null,
        public ?string $teacher = null,
        public ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'announcement',
            'icon' => 'megaphone',
            'tone' => 'blue',
            'title' => $this->title,
            'message' => $this->message,
            'teacher' => $this->teacher,
            'url' => $this->url,
        ];
    }
}
