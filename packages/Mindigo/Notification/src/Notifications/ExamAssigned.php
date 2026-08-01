<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Notifications\Notification;

class ExamAssigned extends Notification
{
    public function __construct(
        public int $examId,
        public string $examTitle,
        public ?string $teacherName,
        public ?string $startsAt,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'exam_assigned',
            'icon' => 'clipboard-document-list',
            'tone' => 'green',
            'title' => __('teacher-exam::app.exam_assigned_notification', ['exam' => $this->examTitle]),
            'message' => __('teacher-exam::app.exam_assigned_message', [
                'teacher' => $this->teacherName ?: __('teacher-exam::app.teacher'),
                'time' => $this->startsAt ?: __('teacher-exam::app.available_now'),
            ]),
            'exam_id' => $this->examId,
            'url' => $this->url,
        ];
    }
}
