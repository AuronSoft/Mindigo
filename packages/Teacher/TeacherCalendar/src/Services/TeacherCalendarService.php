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

    public function period(User $teacher, CarbonImmutable $anchor, string $viewMode, array $filters): array
    {
        [$start, $end, $days] = match ($viewMode) {
            'day' => [$anchor->startOfDay(), $anchor->addDay()->startOfDay(), collect([$anchor->startOfDay()])],
            'month' => [
                $anchor->startOfMonth()->startOfWeek(),
                $anchor->endOfMonth()->endOfWeek()->addDay()->startOfDay(),
                collect(range(0, 41))->map(fn (int $day) => $anchor->startOfMonth()->startOfWeek()->addDays($day)),
            ],
            'schedule' => [$anchor->startOfDay(), $anchor->addDays(30)->endOfDay(), collect()],
            default => [
                $anchor->startOfWeek(),
                $anchor->startOfWeek()->addWeek(),
                collect(range(0, 6))->map(fn (int $day) => $anchor->startOfWeek()->addDays($day)),
            ],
        };
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

        $teachingEvents = $events->where('kind', CalendarEventKind::ClassSession)
            ->filter(fn ($event) => ($event->metadata['teaching_responsibility'] ?? true) === true);

        return [
            'start' => $start,
            'end' => $end,
            'days' => $days,
            'events' => $events,
            'eventsByDay' => $events->groupBy(fn ($event) => $event->startsAt->format('Y-m-d')),
            'summary' => [
                'count' => $events->count(),
                'class_sessions' => $teachingEvents->count(),
                'hours' => round($teachingEvents->sum(
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
