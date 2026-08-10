<?php

namespace Mindigo\AcademicCalendar\Notifications;

use Illuminate\Notifications\Notification;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

class CalendarEventChanged extends Notification
{
    public function __construct(public ClassroomSchedule $schedule, public string $change) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $schedule = $this->schedule;
        $date = $schedule->session_date->format('d/m/Y').' · '.substr($schedule->start_time, 0, 5);
        $isStudent = method_exists($notifiable, 'isStudent') && $notifiable->isStudent();
        $url = $isStudent
            ? route('student.schedule.index', ['date' => $schedule->session_date->toDateString(), 'view' => 'today'])
            : route('teacher.calendar.index', ['date' => $schedule->session_date->toDateString(), 'view' => 'day']);

        return [
            'category' => 'calendar_update',
            'icon' => $this->change === 'cancelled' ? 'x-circle' : 'calendar-days',
            'tone' => $this->change === 'cancelled' ? 'red' : 'green',
            'title' => __('academic-calendar::app.update_'.$this->change, ['session' => $schedule->title]),
            'message' => collect([$schedule->classroom?->name, $date, $schedule->cancel_reason, $schedule->reschedule_reason])->filter()->implode(' · '),
            'event_id' => 'classroom_schedule:'.$schedule->id,
            'classroom_id' => $schedule->classroom_id,
            'audience' => $isStudent ? 'student' : 'teacher',
            'url' => $url,
        ];
    }
}
