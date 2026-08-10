<?php

namespace Mindigo\AcademicCalendar\Adapters;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Mindigo\AcademicCalendar\Contracts\CalendarSourceAdapter;
use Mindigo\AcademicCalendar\Data\CalendarEvent;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
use Mindigo\AcademicCalendar\Enums\CalendarEventSource;
use Mindigo\AcademicCalendar\Enums\CalendarEventStatus;
use Mindigo\AcademicCalendar\Support\CalendarScope;
use Mindigo\TeacherAssignment\Models\Assignment;

final class AssignmentAdapter implements CalendarSourceAdapter
{
    public function __construct(private readonly CalendarScope $scope) {}

    public function events(CalendarQuery $query): Collection
    {
        if (! $query->includes(CalendarEventKind::AssignmentDue)) {
            return collect();
        }

        $classroomIds = $this->scope->classroomIds($query);
        if ($classroomIds->isEmpty()) {
            return collect();
        }

        return Assignment::query()
            ->whereIn('classroom_id', $classroomIds)
            ->when($query->viewer->role === 'student', fn ($builder) => $builder->where('status', 'published'))
            ->where('due_date', '>=', $query->from->utc())
            ->where('due_date', '<', $query->to->utc())
            ->with('classroom:id,name,course_id')
            ->get()
            ->map(function (Assignment $assignment) use ($query): CalendarEvent {
                $dueAt = CarbonImmutable::instance($assignment->due_date)->setTimezone($query->timezone);
                $isTeacher = $query->viewer->role !== 'student';

                return new CalendarEvent(
                    id: 'assignment:'.$assignment->id,
                    source: CalendarEventSource::Assignment,
                    sourceId: $assignment->id,
                    kind: CalendarEventKind::AssignmentDue,
                    status: $assignment->status === 'published' ? CalendarEventStatus::Scheduled : CalendarEventStatus::Draft,
                    title: $assignment->title,
                    startsAt: $dueAt,
                    endsAt: null,
                    timezone: $query->timezone,
                    classroomId: $assignment->classroom_id,
                    courseId: $assignment->classroom?->course_id,
                    ownerId: $assignment->teacher_id,
                    url: $this->routeFor($query),
                    actions: $isTeacher ? ['view', 'edit'] : ['view', 'submit'],
                    metadata: ['classroom_name' => $assignment->classroom?->name, 'allow_late' => $assignment->allow_late],
                );
            });
    }

    private function routeFor(CalendarQuery $query): ?string
    {
        $route = $query->viewer->role === 'student' ? 'student.assignments.index' : 'teacher.assignments.index';

        return Route::has($route) ? route($route) : null;
    }
}
