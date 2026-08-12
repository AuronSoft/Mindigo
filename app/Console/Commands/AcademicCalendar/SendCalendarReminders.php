<?php

namespace App\Console\Commands\AcademicCalendar;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Mindigo\AcademicCalendar\Services\CalendarReminderService;
use Mindigo\Auth\Models\User;

class SendCalendarReminders extends Command
{
    protected $signature = 'calendar:send-reminders {--at= : Testing override in ISO-8601 format}';

    protected $description = 'Send idempotent reminders for upcoming academic calendar events';

    public function handle(CalendarReminderService $reminders): int
    {
        $now = CarbonImmutable::parse($this->option('at') ?: 'now', config('app.timezone'))->startOfMinute();
        $sent = 0;

        User::query()->active()->whereIn('role', ['student', 'teacher'])
            ->with('notificationPreference')->chunkById(100, function ($users) use ($reminders, $now, &$sent): void {
                foreach ($users as $user) {
                    $sent += $reminders->sendFor($user, $now);
                }
            });

        $this->info("Calendar reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
