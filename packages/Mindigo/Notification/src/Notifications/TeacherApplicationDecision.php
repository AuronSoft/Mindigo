<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Notifications\Notification;

class TeacherApplicationDecision extends Notification
{
    public function __construct(
        private readonly string $applicationCode,
        private readonly string $status,
        private readonly ?string $note = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('teacher-onboarding::admin.decision_notification_title'),
            'message' => __('teacher-onboarding::admin.decision_notification_message', [
                'code' => $this->applicationCode,
                'status' => __('teacher-onboarding::admin.statuses.'.$this->status),
            ]),
            'category' => 'teacher_application_decision',
            'icon' => $this->status === 'rejected' ? 'x-circle' : 'information-circle',
            'tone' => $this->status === 'rejected' ? 'red' : 'amber',
            'teacher_application_code' => $this->applicationCode,
            'teacher_application_status' => $this->status,
            'note' => $this->note,
            'url' => route('teacher-applications.create'),
        ];
    }
}
