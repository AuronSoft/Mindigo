<?php

namespace Mindigo\AcademicCalendar\Services;

use Illuminate\Support\Collection;
use Mindigo\AcademicCalendar\Contracts\CalendarSourceAdapter;
use Mindigo\AcademicCalendar\Data\CalendarEvent;
use Mindigo\AcademicCalendar\Data\CalendarQuery;

final class AcademicCalendarService
{
    /** @param iterable<CalendarSourceAdapter> $adapters */
    public function __construct(private readonly iterable $adapters) {}

    /** @return Collection<int, CalendarEvent> */
    public function events(CalendarQuery $query): Collection
    {
        return collect($this->adapters)
            ->flatMap(fn (CalendarSourceAdapter $adapter) => $adapter->events($query))
            ->sortBy(fn (CalendarEvent $event) => $event->startsAt->getTimestamp(), SORT_NUMERIC)
            ->values();
    }
}
