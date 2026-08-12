<?php

namespace Mindigo\TeacherLiveSession\Notifications;

use Illuminate\Notifications\Notification;

final class LiveSessionProviderChanged extends Notification
{
    public function __construct(
        public readonly int $sessionId,
        public readonly string $title,
        public readonly string $classroom,
        public readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'live_session',
            'icon' => 'video-camera',
            'tone' => 'green',
            'title' => __('teacher-live-session::app.provider_changed_notification_title'),
            'message' => __('teacher-live-session::app.provider_changed_notification_message', [
                'session' => $this->title,
                'classroom' => $this->classroom,
            ]),
            'url' => $this->url,
            'live_session_id' => $this->sessionId,
        ];
    }
}
