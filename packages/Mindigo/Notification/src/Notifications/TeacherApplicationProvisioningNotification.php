<?php

namespace Mindigo\Notification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeacherApplicationProvisioningNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $applicationCode,
        private readonly string $action,
        private readonly ?string $note = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => __("teacher-onboarding::provisioning.notifications.{$this->action}.title"),
            'message' => __("teacher-onboarding::provisioning.notifications.{$this->action}.message", [
                'code' => $this->applicationCode,
                'note' => $this->note,
            ]),
            'category' => 'teacher_application_provisioning',
            'icon' => $this->icon(),
            'tone' => $this->tone(),
            'application_code' => $this->applicationCode,
            'action' => $this->action,
            'note' => $this->note,
            'url' => route('teacher-applications.create'),
        ];
    }

    private function icon(): string
    {
        return match ($this->action) {
            'approved' => 'heroicon-o-academic-cap',
            'suspended' => 'heroicon-o-pause-circle',
            'revoked' => 'heroicon-o-no-symbol',
            default => 'heroicon-o-bell',
        };
    }

    private function tone(): string
    {
        return match ($this->action) {
            'approved' => 'green',
            'suspended' => 'amber',
            'revoked' => 'red',
            default => 'blue',
        };
    }
}
