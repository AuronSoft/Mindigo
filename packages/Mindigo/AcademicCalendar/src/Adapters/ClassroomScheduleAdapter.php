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
        if ($classroomIds->isEmpty() && $query->viewer->role !== 'teacher') {
            return collect();
        }

        return ClassroomSchedule::query()
            ->where(function ($builder) use ($classroomIds, $query): void {
                $builder->whereIn('classroom_id', $classroomIds);
                if ($query->viewer->role === 'teacher' && $query->classroomIds === []) {
                    $builder->orWhere('substitute_teacher_id', $query->viewer->id);
                }
            })
            ->when($query->viewer->role === 'student', fn ($builder) => $builder->where('status', '!=', ClassroomSchedule::STATUS_DRAFT))
            ->whereDate('session_date', '>=', $query->from->setTimezone($query->timezone)->toDateString())
            ->whereDate('session_date', '<=', $query->to->setTimezone($query->timezone)->toDateString())
            ->with(['classroom:id,name,course_id,teacher_id', 'attendanceSession', 'substituteTeacher:id,name,email'])
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
                $isOwner = $schedule->classroom?->teacher_id === (int) $query->viewer->id;
                $isSubstitute = $schedule->substitute_teacher_id === (int) $query->viewer->id;
                $attendance = $schedule->attendanceSession;
                $attendanceOpen = $attendance?->isOpen() ?? false;

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
                    url: ! $isTeacher || $isOwner ? $this->routeFor($query, $schedule->classroom_id) : null,
                    actions: $isOwner ? ['view', 'edit', 'reschedule', 'cancel'] : ['view'],
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
                        'can_manage_session' => $isOwner,
                        'substitute_teacher_id' => $schedule->substitute_teacher_id,
                        'substitute_teacher_name' => $schedule->substituteTeacher?->name,
                        'substitute_status' => $schedule->substitute_status,
                        'substitute_response_note' => $schedule->substitute_response_note,
                        'substitute_response_url' => $isSubstitute && $schedule->substitute_status === ClassroomSchedule::SUBSTITUTE_PENDING ? route('teacher.calendar.sessions.substitute.respond', $schedule) : null,
                        'teaching_responsibility' => $isOwner || ($isSubstitute && $schedule->substitute_status === ClassroomSchedule::SUBSTITUTE_ACCEPTED),
                        'update_url' => $isOwner ? route('teacher.calendar.sessions.update', $schedule) : null,
                        'reschedule_url' => $isOwner ? route('teacher.calendar.sessions.reschedule', $schedule) : null,
                        'complete_url' => $isOwner ? route('teacher.calendar.sessions.complete', $schedule) : null,
                        'attendance_status' => $attendanceOpen ? 'open' : ($attendance ? 'closed' : 'not_open'),
                        'attendance_code' => $isOwner && $attendanceOpen ? $attendance->code : null,
                        'attendance_expires_at' => $attendanceOpen ? $attendance->expires_at?->format('H:i') : null,
                        'attendance_open_url' => $isOwner && $schedule->status === ClassroomSchedule::STATUS_SCHEDULED ? route('teacher.calendar.sessions.attendance.open', $schedule) : null,
                        'attendance_close_url' => $isOwner && $attendanceOpen ? route('teacher.classrooms.attendance.code.close', $attendance) : null,
                        'attendance_url' => ! $isTeacher || $isOwner ? $this->attendanceRouteFor($query, $schedule) : null,
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

    private function attendanceRouteFor(CalendarQuery $query, ClassroomSchedule $schedule): ?string
    {
        $route = $query->viewer->role === 'student' ? 'student.classrooms.show' : 'teacher.classrooms.show';

        return Route::has($route) ? route($route, [
            $schedule->classroom_id,
            'tab' => 'attendance',
            'attendance_date' => $schedule->session_date->toDateString(),
            'attendance_schedule_id' => $schedule->id,
        ]) : null;
    }
}
