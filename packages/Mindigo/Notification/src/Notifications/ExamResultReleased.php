<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Notifications\Notification;

class ExamResultReleased extends Notification
{
    public function __construct(
        public int $attemptId,
        public string $sessionTitle,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'exam_result_released',
            'icon' => 'academic-cap',
            'tone' => 'green',
            'title' => __('Mindigo-exam-management::app.grading.result_notification_title'),
            'message' => __('Mindigo-exam-management::app.grading.result_notification_message', ['session' => $this->sessionTitle]),
            'exam_session_attempt_id' => $this->attemptId,
            'url' => $this->url,
        ];
    }
}
