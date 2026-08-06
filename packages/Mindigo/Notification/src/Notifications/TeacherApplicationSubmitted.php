<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Notifications\Notification;

class TeacherApplicationSubmitted extends Notification
{
    public function __construct(
        public int $applicationId,
        public string $applicationCode,
        public string $applicantName,
        public string $applicationType,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'teacher_application_submitted',
            'icon' => 'user-plus',
            'tone' => 'green',
            'title' => __('teacher-onboarding::application.notification_title'),
            'message' => __('teacher-onboarding::application.notification_message', [
                'name' => $this->applicantName,
                'type' => __('teacher-onboarding::application.types.'.$this->applicationType),
            ]),
            'teacher_application_id' => $this->applicationId,
            'application_code' => $this->applicationCode,
            'url' => route('dashboard'),
        ];
    }
}
