<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Notifications\Notification;

class CourseAssigned extends Notification
{
    public function __construct(
        public int $courseId,
        public string $courseName,
        public string $teacherName,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'course_assigned',
            'icon' => 'book-open',
            'tone' => 'green',
            'title' => __('teacher-course::learning.notification_title', ['course' => $this->courseName]),
            'message' => __('teacher-course::learning.notification_message', ['teacher' => $this->teacherName]),
            'course_id' => $this->courseId,
            'url' => $this->url,
        ];
    }
}
