<?php

namespace Mindigo\AcademicCalendar\Services;

use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

class CalendarNotificationRecipientService
{
    /** @return Collection<int, User> */
    public function forSchedule(ClassroomSchedule $schedule): Collection
    {
        $schedule->loadMissing('classroom:id,teacher_id,assistant_id');
        $classroom = $schedule->classroom;
        $participantIds = $classroom->students()
            ->where('classroom_students.status', 'active')
            ->pluck('users.id')
            ->push($classroom->teacher_id)
            ->push($classroom->assistant_id)
            ->push($schedule->substitute_teacher_id)
            ->filter()
            ->unique()
            ->values();

        return User::query()
            ->active()
            ->whereIn('id', $participantIds)
            ->where(fn ($query) => $query->whereDoesntHave('notificationPreference')->orWhereHas(
                'notificationPreference', fn ($preference) => $preference->where('notif_calendar_updates', true)
            ))
            ->get();
    }
}
