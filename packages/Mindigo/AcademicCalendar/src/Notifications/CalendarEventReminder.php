<?php

namespace Mindigo\AcademicCalendar\Notifications;

use Illuminate\Notifications\Notification;
use Mindigo\AcademicCalendar\Data\CalendarEvent;

class CalendarEventReminder extends Notification
{
    public function __construct(public CalendarEvent $event, public string $reminderKey) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'calendar_reminder',
            'icon' => 'bell-alert',
            'tone' => 'amber',
            'title' => __('academic-calendar::app.reminder_'.$this->reminderKey, ['event' => $this->event->title]),
            'message' => collect([
                $this->event->metadata['classroom_name'] ?? null,
                $this->event->startsAt->format('d/m/Y · H:i'),
            ])->filter()->implode(' · '),
            'event_id' => $this->event->id,
            'url' => $this->event->url,
        ];
    }
}
