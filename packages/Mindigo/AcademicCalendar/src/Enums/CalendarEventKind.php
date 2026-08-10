<?php

namespace Mindigo\AcademicCalendar\Enums;

enum CalendarEventKind: string
{
    case ClassSession = 'class_session';
    case AssignmentDue = 'assignment_due';
    case ExamWindow = 'exam_window';
    case LiveSession = 'live_session';
    case AcademicClosure = 'academic_closure';
}
