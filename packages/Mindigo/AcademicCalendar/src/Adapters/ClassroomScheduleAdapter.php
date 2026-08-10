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
use Mindigo\AcademicCalendar\Support\MapsCalendarStatus;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

final class ClassroomScheduleAdapter implements CalendarSourceAdapter
{
    use MapsCalendarStatus;

    public function __construct(private readonly CalendarScope $scope) {}

    public function events(CalendarQuery $query): Collection
    {
        if (! $query->includes(CalendarEventKind::ClassSession)) {
            return collect();
        }

        $classroomIds = $this->scope->classroomIds($query);
        if ($classroomIds->isEmpty()) {
            return collect();
        }

        return ClassroomSchedule::query()
            ->whereIn('classroom_id', $classroomIds)
            ->when($query->viewer->role === 'student', fn ($builder) => $builder->where('status', '!=', ClassroomSchedule::STATUS_DRAFT))
            ->whereDate('session_date', '>=', $query->from->setTimezone($query->timezone)->toDateString())
            ->whereDate('session_date', '<=', $query->to->setTimezone($query->timezone)->toDateString())
            ->with('classroom:id,name,course_id,teacher_id')
            ->get()
            ->map(function (ClassroomSchedule $schedule) use ($query): CalendarEvent {
                $startsAt = CarbonImmutable::parse(
                    $schedule->session_date->format('Y-m-d').' '.$schedule->start_time,
                    $query->timezone,
                );
                $endsAt = $schedule->end_time
                    ? CarbonImmutable::parse($schedule->session_date->format('Y-m-d').' '.$schedule->end_time, $query->timezone)
                    : null;
                $isTeacher = $query->viewer->role !== 'student';

                return new CalendarEvent(
                    id: 'classroom_schedule:'.$schedule->id,
                    source: CalendarEventSource::ClassroomSchedule,
                    sourceId: $schedule->id,
                    kind: CalendarEventKind::ClassSession,
                    status: match ($schedule->status) {
                        ClassroomSchedule::STATUS_DRAFT => CalendarEventStatus::Draft,
                        ClassroomSchedule::STATUS_CANCELLED, ClassroomSchedule::STATUS_RESCHEDULED => CalendarEventStatus::Cancelled,
                        ClassroomSchedule::STATUS_COMPLETED => CalendarEventStatus::Completed,
                        default => $this->temporalStatus($startsAt, $endsAt),
                    },
                    title: $schedule->title,
                    startsAt: $startsAt,
                    endsAt: $endsAt,
                    timezone: $query->timezone,
                    classroomId: $schedule->classroom_id,
                    courseId: $schedule->classroom?->course_id,
                    lessonId: $schedule->lesson_id,
                    ownerId: $schedule->classroom?->teacher_id,
                    url: $this->routeFor($query, $schedule->classroom_id),
                    actions: $isTeacher ? ['view', 'edit', 'reschedule', 'cancel'] : ['view'],
                    metadata: array_filter([
                        'classroom_name' => $schedule->classroom?->name,
                        'session_type' => $schedule->type,
                        'delivery_mode' => $schedule->delivery_mode,
                        'location' => $schedule->location,
                        'meeting_url' => $schedule->meeting_url,
                        'description' => $schedule->description,
                        'makeup_reason' => $schedule->type === ClassroomSchedule::TYPE_MAKEUP ? $schedule->makeup_reason : null,
                        'cancel_reason' => $schedule->cancel_reason,
                        'reschedule_reason' => $schedule->reschedule_reason,
                        'lifecycle_status' => $schedule->status,
                        'substitute_teacher_id' => $schedule->substitute_teacher_id,
                        'update_url' => $isTeacher ? route('teacher.calendar.sessions.update', $schedule) : null,
                        'reschedule_url' => $isTeacher ? route('teacher.calendar.sessions.reschedule', $schedule) : null,
                        'complete_url' => $isTeacher ? route('teacher.calendar.sessions.complete', $schedule) : null,
                    ], fn ($value) => $value !== null),
                );
            })
            ->filter(fn (CalendarEvent $event) => $event->startsAt->lessThan($query->to) && ($event->endsAt ?? $event->startsAt)->greaterThanOrEqualTo($query->from))
            ->values();
    }

    private function routeFor(CalendarQuery $query, int $classroomId): ?string
    {
        $route = $query->viewer->role === 'student' ? 'student.classrooms.show' : 'teacher.classrooms.show';

        return Route::has($route) ? route($route, $classroomId) : null;
    }
}
