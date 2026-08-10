<?php

namespace Mindigo\AcademicCalendar\Enums;

enum CalendarEventSource: string
{
    case ClassroomSchedule = 'classroom_schedule';
    case Assignment = 'assignment';
    case Exam = 'exam';
    case LiveSession = 'live_session';
    case AcademicException = 'academic_exception';
}
