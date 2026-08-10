<?php

namespace Mindigo\AcademicCalendar\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Mindigo\AcademicCalendar\Data\CalendarEvent;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\AcademicCalendar\Enums\CalendarEventStatus;
use Mindigo\AcademicCalendar\Models\CalendarReminder;
use Mindigo\AcademicCalendar\Notifications\CalendarEventReminder;
use Mindigo\Auth\Models\User;

class CalendarReminderService
{
    private const WINDOW_MINUTES = 5;

    private const LEADS = ['24h' => 1440, '60m' => 60];

    public function __construct(private readonly AcademicCalendarService $calendar) {}

    public function sendFor(User $user, CarbonImmutable $now): int
    {
        if ($user->notificationPreference && ! $user->notificationPreference->notif_calendar_reminders) {
            return 0;
        }

        $events = $this->calendar->events(new CalendarQuery(
            viewer: $user,
            from: $now,
            to: $now->addHours(25),
            timezone: config('app.timezone', 'Asia/Ho_Chi_Minh'),
        ));
        $sent = 0;

        foreach ($events as $event) {
            if ($event->status === CalendarEventStatus::Cancelled) {
                continue;
            }

            foreach (self::LEADS as $key => $leadMinutes) {
                if (! $this->isDue($event, $now, $leadMinutes)) {
                    continue;
                }

                $sent += $this->deliver($user, $event, $key, $now);
            }
        }

        return $sent;
    }

    private function isDue(CalendarEvent $event, CarbonImmutable $now, int $leadMinutes): bool
    {
        $minutes = $now->diffInMinutes($event->startsAt, false);

        return $minutes >= $leadMinutes && $minutes < $leadMinutes + self::WINDOW_MINUTES;
    }

    private function deliver(User $user, CalendarEvent $event, string $key, CarbonImmutable $now): int
    {
        return DB::transaction(function () use ($user, $event, $key, $now): int {
            $delivery = CalendarReminder::query()->firstOrCreate(
                ['user_id' => $user->id, 'event_id' => $event->id, 'reminder_key' => $key],
                ['scheduled_for' => $event->startsAt->subMinutes(self::LEADS[$key])],
            );

            if ($delivery->sent_at) {
                return 0;
            }

            $user->notify(new CalendarEventReminder($event, $key));
            $delivery->update(['sent_at' => $now]);

            return 1;
        }, 3);
    }
}
