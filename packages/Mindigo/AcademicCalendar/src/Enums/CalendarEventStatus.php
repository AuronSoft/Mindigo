<?php

namespace Mindigo\AcademicCalendar\Enums;

enum CalendarEventStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
