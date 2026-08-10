<?php

namespace Mindigo\TeacherCalendar\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
use Mindigo\AcademicCalendar\Services\AcademicCalendarService;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;

class TeacherCalendarService
{
    public function __construct(private readonly AcademicCalendarService $calendar) {}

    public function week(User $teacher, CarbonImmutable $anchor, array $filters): array
    {
        $start = $anchor->startOfWeek();
        $end = $start->addWeek();
        $classroomIds = isset($filters['classroom_id']) ? [(int) $filters['classroom_id']] : [];
        $kinds = collect($filters['kinds'] ?? [])->map(fn (string $kind) => CalendarEventKind::from($kind))->all();
        $events = $this->calendar->events(new CalendarQuery(
            viewer: $teacher,
            from: $start,
            to: $end,
            timezone: config('app.timezone', 'Asia/Ho_Chi_Minh'),
            kinds: $kinds,
            classroomIds: $classroomIds,
        ));

        return [
            'start' => $start,
            'end' => $end,
            'days' => collect(range(0, 6))->map(fn (int $day) => $start->addDays($day)),
            'events' => $events,
            'eventsByDay' => $events->groupBy(fn ($event) => $event->startsAt->format('Y-m-d')),
            'summary' => [
                'count' => $events->count(),
                'class_sessions' => $events->where('kind', CalendarEventKind::ClassSession)->count(),
                'hours' => round($events->where('kind', CalendarEventKind::ClassSession)->sum(
                    fn ($event) => $event->endsAt ? $event->startsAt->diffInMinutes($event->endsAt) / 60 : 0
                ), 1),
            ],
        ];
    }

    /** @return Collection<int, Classroom> */
    public function classrooms(User $teacher): Collection
    {
        return Classroom::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['course.chapters.lessons', 'subject'])
            ->orderBy('name')
            ->get();
    }
}
