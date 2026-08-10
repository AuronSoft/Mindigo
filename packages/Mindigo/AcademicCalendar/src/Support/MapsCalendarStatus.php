<?php

namespace Mindigo\AcademicCalendar\Support;

use Carbon\CarbonImmutable;
use Mindigo\AcademicCalendar\Enums\CalendarEventStatus;

trait MapsCalendarStatus
{
    private function temporalStatus(CarbonImmutable $startsAt, ?CarbonImmutable $endsAt): CalendarEventStatus
    {
        $now = CarbonImmutable::now($startsAt->getTimezone());

        if ($endsAt && $endsAt->lessThanOrEqualTo($now)) {
            return CalendarEventStatus::Completed;
        }

        if ($startsAt->lessThanOrEqualTo($now) && (! $endsAt || $endsAt->isAfter($now))) {
            return CalendarEventStatus::InProgress;
        }

        return CalendarEventStatus::Scheduled;
    }
}
