<?php

namespace Mindigo\AcademicCalendar\Adapters;

use App\Support\AcademicCalendar\CalendarScope;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Mindigo\AcademicCalendar\Contracts\CalendarSourceAdapter;
use Mindigo\AcademicCalendar\Data\CalendarEvent;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
use Mindigo\AcademicCalendar\Enums\CalendarEventSource;
use Mindigo\AcademicCalendar\Enums\CalendarEventStatus;
use Mindigo\AcademicCalendar\Models\AcademicCalendarException;
use Mindigo\TeacherClassroom\Models\Classroom;

final class AcademicExceptionAdapter implements CalendarSourceAdapter
{
    public function __construct(private readonly CalendarScope $scope) {}

    public function events(CalendarQuery $query): Collection
    {
        if (! $query->includes(CalendarEventKind::AcademicClosure)) {
            return collect();
        }

        $classroomIds = $this->scope->classroomIds($query);
        $courseIds = Classroom::query()->whereIn('id', $classroomIds)->whereNotNull('course_id')->pluck('course_id')->unique();

        return AcademicCalendarException::query()
            ->where('kind', AcademicCalendarException::KIND_NO_CLASS)
            ->whereDate('exception_date', '>=', $query->from->setTimezone($query->timezone)->toDateString())
            ->whereDate('exception_date', '<=', $query->to->setTimezone($query->timezone)->toDateString())
            ->where(function ($builder) use ($classroomIds, $courseIds, $query): void {
                $builder->where(fn ($global) => $global->whereNull('course_id')->whereNull('classroom_id'));
                if ($query->viewer->isAdmin()) {
                    $builder->orWhereNotNull('id');
                } else {
                    $builder->orWhereIn('classroom_id', $classroomIds)
                        ->orWhere(fn ($course) => $course->whereNull('classroom_id')->whereIn('course_id', $courseIds));
                }
            })
            ->with(['course:id,name', 'classroom:id,name,course_id'])
            ->get()
            ->map(function (AcademicCalendarException $exception) use ($query): CalendarEvent {
                $startsAt = CarbonImmutable::parse($exception->exception_date->format('Y-m-d'), $query->timezone)->startOfDay();
                $scope = $exception->classroom_id ? 'classroom' : ($exception->course_id ? 'course' : 'global');

                return new CalendarEvent(
                    id: 'academic_exception:'.$exception->id,
                    source: CalendarEventSource::AcademicException,
                    sourceId: $exception->id,
                    kind: CalendarEventKind::AcademicClosure,
                    status: CalendarEventStatus::Scheduled,
                    title: $exception->title,
                    startsAt: $startsAt,
                    endsAt: $startsAt->addDay(),
                    timezone: $query->timezone,
                    classroomId: $exception->classroom_id,
                    courseId: $exception->course_id ?? $exception->classroom?->course_id,
                    ownerId: $exception->created_by,
                    actions: ['view'],
                    metadata: array_filter([
                        'all_day' => true,
                        'exception_scope' => $scope,
                        'reason' => $exception->reason,
                        'classroom_name' => $exception->classroom?->name,
                        'course_name' => $exception->course?->name,
                    ], fn ($value) => $value !== null),
                );
            })
            ->values();
    }
}
