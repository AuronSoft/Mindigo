<?php

namespace Mindigo\AcademicCalendar\Data;

use Carbon\CarbonImmutable;
use InvalidArgumentException;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
use Mindigo\Auth\Models\User;

final readonly class CalendarQuery
{
    /**
     * @param  list<CalendarEventKind>  $kinds
     * @param  list<int>  $classroomIds
     */
    public function __construct(
        public User $viewer,
        public CarbonImmutable $from,
        public CarbonImmutable $to,
        public string $timezone = 'Asia/Ho_Chi_Minh',
        public array $kinds = [],
        public array $classroomIds = [],
    ) {
        if ($to->lessThanOrEqualTo($from)) {
            throw new InvalidArgumentException('Calendar range end must be after its start.');
        }

        if ($from->diffInDays($to) > 366) {
            throw new InvalidArgumentException('Calendar range cannot exceed 366 days.');
        }

        if (! in_array($viewer->role, ['teacher', 'student', 'admin'], true)) {
            throw new InvalidArgumentException('The viewer role cannot access the academic calendar.');
        }

        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            throw new InvalidArgumentException('Calendar timezone must be a valid IANA timezone.');
        }
    }

    public function includes(CalendarEventKind $kind): bool
    {
        return $this->kinds === [] || in_array($kind, $this->kinds, true);
    }
}
