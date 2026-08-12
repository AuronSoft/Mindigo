<?php

namespace Mindigo\AcademicCalendar\Services;

use Illuminate\Support\Collection;
use Mindigo\AcademicCalendar\Contracts\CalendarSourceAdapter;
use Mindigo\AcademicCalendar\Data\CalendarEvent;
use Mindigo\AcademicCalendar\Data\CalendarQuery;

final class AcademicCalendarService
{
    /** @var array<int, CalendarSourceAdapter> */
    private readonly array $adapters;

    /** @param iterable<CalendarSourceAdapter> $adapters */
    public function __construct(iterable $adapters)
    {
        $this->adapters = is_array($adapters) ? array_values($adapters) : iterator_to_array($adapters, false);
    }

    /** @return Collection<int, CalendarEvent> */
    public function events(CalendarQuery $query): Collection
    {
        return collect($this->adapters)
            ->flatMap(fn (CalendarSourceAdapter $adapter) => $adapter->events($query))
            ->sortBy(fn (CalendarEvent $event) => $event->startsAt->getTimestamp(), SORT_NUMERIC)
            ->values();
    }
}
