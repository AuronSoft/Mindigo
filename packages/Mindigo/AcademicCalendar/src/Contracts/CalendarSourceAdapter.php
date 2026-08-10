<?php

namespace Mindigo\AcademicCalendar\Contracts;

use Illuminate\Support\Collection;
use Mindigo\AcademicCalendar\Data\CalendarEvent;
use Mindigo\AcademicCalendar\Data\CalendarQuery;

interface CalendarSourceAdapter
{
    /** @return Collection<int, CalendarEvent> */
    public function events(CalendarQuery $query): Collection;
}
