<?php

namespace Mindigo\AcademicCalendar\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Notification;
use Mindigo\AcademicCalendar\Notifications\CalendarEventChanged;
use Mindigo\AcademicCalendar\Services\CalendarNotificationRecipientService;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

class ClassroomScheduleObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly CalendarNotificationRecipientService $recipients) {}

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

        if ($schedule->wasChanged('status') && ($schedule->getPrevious()['status'] ?? null) === ClassroomSchedule::STATUS_DRAFT && $schedule->status === ClassroomSchedule::STATUS_SCHEDULED) {
            $this->notify($schedule, 'created');

            return;
        }

        if ($schedule->status !== ClassroomSchedule::STATUS_DRAFT && $schedule->wasChanged(['session_date', 'start_time', 'end_time', 'location', 'meeting_url'])) {
            $this->notify($schedule, 'rescheduled');

            return;
        }

        if ($schedule->status === ClassroomSchedule::STATUS_SCHEDULED && $schedule->wasChanged(['title', 'lesson_id', 'delivery_mode', 'substitute_teacher_id'])) {
            $this->notify($schedule, 'updated');
        }
    }

    private function notify(ClassroomSchedule $schedule, string $change): void
    {
        $schedule->loadMissing('classroom:id,name,teacher_id,assistant_id');
        Notification::send($this->recipients->forSchedule($schedule), new CalendarEventChanged($schedule, $change));
    }
}
