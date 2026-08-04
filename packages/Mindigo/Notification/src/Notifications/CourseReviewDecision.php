<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Notifications\Notification;

class CourseReviewDecision extends Notification
{
    public function __construct(
        public int $courseId,
        public string $courseName,
        public string $decision,
        public ?string $note,
        public string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $approved = $this->decision === 'approved';

        return [
            'category' => 'course_review_decision',
            'icon' => $approved ? 'clipboard-check' : 'pencil-square',
            'tone' => $approved ? 'green' : 'amber',
            'title' => __($approved ? 'teacher-course::admin-review.notification_approved_title' : 'teacher-course::admin-review.notification_changes_title', ['course' => $this->courseName]),
            'message' => $approved ? __('teacher-course::admin-review.notification_approved_message') : $this->note,
            'course_id' => $this->courseId,
            'url' => $this->url,
        ];
    }
}
