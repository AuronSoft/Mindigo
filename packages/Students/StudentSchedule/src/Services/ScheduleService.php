<?php

namespace Mindigo\StudentSchedule\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Mindigo\AcademicCalendar\Data\CalendarEvent;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
use Mindigo\AcademicCalendar\Enums\CalendarEventStatus;
use Mindigo\AcademicCalendar\Services\AcademicCalendarService;
use Mindigo\Auth\Models\User;

class ScheduleService
{
    public function __construct(private readonly AcademicCalendarService $calendar) {}

    public function workspace(User $student, CarbonImmutable $anchor, string $view, array $filters = []): array
    {
        [$from, $to] = $this->range($anchor, $view);
        $kinds = collect($filters['kinds'] ?? [])->map(fn (string $kind) => CalendarEventKind::from($kind))->all();
        $events = $this->calendar->events(new CalendarQuery(
            viewer: $student,
            from: $from,
            to: $to,
            timezone: config('app.timezone', 'Asia/Ho_Chi_Minh'),
            kinds: $kinds,
        ));

        $agendaFrom = $anchor->isToday() ? CarbonImmutable::now()->subHour() : $anchor->startOfDay();
        $agenda = $this->calendar->events(new CalendarQuery(
            viewer: $student,
            from: $agendaFrom,
            to: $anchor->endOfDay()->addDays(45),
            timezone: config('app.timezone', 'Asia/Ho_Chi_Minh'),
            kinds: $kinds,
        ));

        return [
            'viewMode' => $view,
            'anchor' => $anchor,
            'from' => $from,
            'to' => $to,
            'days' => collect(range(0, max(0, $from->diffInDays($to) - 1)))->map(fn (int $day) => $from->addDays($day)),
            'events' => $events,
            'eventsByDay' => $events->groupBy(fn (CalendarEvent $event) => $event->startsAt->format('Y-m-d')),
            'agenda' => $agenda->take(20)->values(),
            'priorities' => [
                'session' => $this->nextActive($agenda, CalendarEventKind::ClassSession),
                'assignment' => $this->nextActive($agenda, CalendarEventKind::AssignmentDue),
                'exam' => $this->nextActive($agenda, CalendarEventKind::ExamWindow),
            ],
            'summary' => [
                'events' => $events->count(),
                'sessions' => $events->where('kind', CalendarEventKind::ClassSession)->where('status', '!=', CalendarEventStatus::Cancelled)->count(),
                'deadlines' => $events->whereIn('kind', [CalendarEventKind::AssignmentDue, CalendarEventKind::ExamWindow])->count(),
            ],
        ];
    }

    private function range(CarbonImmutable $anchor, string $view): array
    {
        return match ($view) {
            'today' => [$anchor->startOfDay(), $anchor->addDay()->startOfDay()],
            'month' => [$anchor->startOfMonth()->startOfWeek(), $anchor->endOfMonth()->endOfWeek()->addDay()->startOfDay()],
            'schedule' => [$anchor->startOfDay(), $anchor->addDays(45)->endOfDay()],
            default => [$anchor->startOfWeek(), $anchor->startOfWeek()->addWeek()],
        };
    }

    private function nextActive(Collection $events, CalendarEventKind $kind): ?CalendarEvent
    {
        return $events->first(fn (CalendarEvent $event) => $event->kind === $kind && $event->status !== CalendarEventStatus::Cancelled);
    }
}
