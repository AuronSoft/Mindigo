<?php

namespace Mindigo\AcademicCalendar\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Notification;
use Mindigo\AcademicCalendar\Notifications\CalendarEventChanged;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

class ClassroomScheduleObserver implements ShouldHandleEventsAfterCommit
{
    public function created(ClassroomSchedule $schedule): void
    {
        if ($schedule->status !== ClassroomSchedule::STATUS_DRAFT) {
            $this->notify($schedule, $schedule->rescheduled_from_id ? 'rescheduled' : 'created');
        }
    }

    public function updated(ClassroomSchedule $schedule): void
    {
        if ($schedule->wasChanged('status') && $schedule->status === ClassroomSchedule::STATUS_CANCELLED) {
            $this->notify($schedule, 'cancelled');

            return;
        }

        if ($schedule->status !== ClassroomSchedule::STATUS_DRAFT && $schedule->wasChanged(['session_date', 'start_time', 'end_time', 'location', 'meeting_url'])) {
            $this->notify($schedule, 'rescheduled');
        }
    }

    private function notify(ClassroomSchedule $schedule, string $change): void
    {
        $schedule->loadMissing('classroom:id,name');
        $recipients = $schedule->classroom->students()->active()
            ->where('classroom_students.status', 'active')
            ->where(fn ($query) => $query->whereDoesntHave('notificationPreference')->orWhereHas(
                'notificationPreference', fn ($preference) => $preference->where('notif_calendar_updates', true)
            ))->get();

        Notification::send($recipients, new CalendarEventChanged($schedule, $change));
    }
}
