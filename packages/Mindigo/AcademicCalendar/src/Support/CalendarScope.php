<?php

namespace Mindigo\AcademicCalendar\Support;

use Illuminate\Support\Collection;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\TeacherClassroom\Models\Classroom;

final class CalendarScope
{
    /** @return Collection<int, int> */
    public function classroomIds(CalendarQuery $query): Collection
    {
        $classrooms = Classroom::query()->select('classrooms.id');

        if ($query->viewer->role === 'teacher') {
            $classrooms->where(function ($builder) use ($query): void {
                $builder->where('teacher_id', $query->viewer->id)
                    ->orWhere('assistant_id', $query->viewer->id)
                    ->orWhereHas('schedules', fn ($schedules) => $schedules->where('substitute_teacher_id', $query->viewer->id));
            });
        } elseif ($query->viewer->role === 'student') {
            $classrooms->whereHas('students', fn ($students) => $students
                ->where('student_id', $query->viewer->id)
                ->where('classroom_students.status', 'active'));
        }

        if ($query->classroomIds !== []) {
            $classrooms->whereIn('classrooms.id', $query->classroomIds);
        }

        return $classrooms->pluck('classrooms.id')->map(fn ($id) => (int) $id)->values();
    }
}
