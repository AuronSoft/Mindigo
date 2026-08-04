<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Notifications\Notification;

class CourseSubmittedForReview extends Notification
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
            'category' => 'course_review_submitted',
            'icon' => 'clipboard-check',
            'tone' => 'amber',
            'title' => __('teacher-course::admin-review.notification_submitted_title', ['course' => $this->courseName]),
            'message' => __('teacher-course::admin-review.notification_submitted_message', ['teacher' => $this->teacherName]),
            'course_id' => $this->courseId,
            'url' => $this->url,
        ];
    }
}
