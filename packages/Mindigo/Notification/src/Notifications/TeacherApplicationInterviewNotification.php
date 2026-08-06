<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Notifications\Notification;

class TeacherApplicationInterviewNotification extends Notification
{
    public function __construct(
        private readonly string $applicationCode,
        private readonly string $event,
        private readonly string $scheduledAt,
        private readonly ?string $result = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('teacher-onboarding::interview.notifications.'.$this->event.'.title'),
            'message' => __('teacher-onboarding::interview.notifications.'.$this->event.'.message', [
                'code' => $this->applicationCode,
                'time' => $this->scheduledAt,
                'result' => $this->result ? __('teacher-onboarding::interview.results.'.$this->result) : null,
            ]),
            'category' => 'teacher_application_interview',
            'icon' => $this->event === 'result' ? 'clipboard-document-check' : 'video-camera',
            'tone' => $this->event === 'result' ? 'green' : 'blue',
            'teacher_application_code' => $this->applicationCode,
            'interview_event' => $this->event,
            'interview_result' => $this->result,
            'url' => route('teacher-applications.create'),
        ];
    }
}
