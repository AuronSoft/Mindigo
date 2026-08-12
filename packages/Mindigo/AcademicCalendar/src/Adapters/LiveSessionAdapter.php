<?php

namespace Mindigo\AcademicCalendar\Adapters;

use App\Support\AcademicCalendar\CalendarScope;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Mindigo\AcademicCalendar\Contracts\CalendarSourceAdapter;
use Mindigo\AcademicCalendar\Data\CalendarEvent;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
use Mindigo\AcademicCalendar\Enums\CalendarEventSource;
use Mindigo\AcademicCalendar\Enums\CalendarEventStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;

final class LiveSessionAdapter implements CalendarSourceAdapter
{
    public function __construct(private readonly CalendarScope $scope) {}

    public function events(CalendarQuery $query): Collection
    {
        if (! $query->includes(CalendarEventKind::LiveSession)) {
            return collect();
        }

        $classroomIds = $this->scope->classroomIds($query);
        if ($classroomIds->isEmpty()) {
            return collect();
        }

        return LiveSession::query()
            ->whereIn('classroom_id', $classroomIds)
            ->whereNull('classroom_schedule_id')
            ->where('scheduled_start', '<', $query->to->utc())
            ->where(fn ($builder) => $builder->whereNull('scheduled_end')->orWhere('scheduled_end', '>=', $query->from->utc()))
            ->with('classroom:id,name,course_id')
            ->get()
            ->map(function (LiveSession $session) use ($query): CalendarEvent {
                $startsAt = CarbonImmutable::instance($session->scheduled_start)->setTimezone($query->timezone);
                $endsAt = $session->scheduled_end
                    ? CarbonImmutable::instance($session->scheduled_end)->setTimezone($query->timezone)
                    : null;
                $status = match ($session->status) {
                    'cancelled' => CalendarEventStatus::Cancelled,
                    'ended' => CalendarEventStatus::Completed,
                    'live' => CalendarEventStatus::InProgress,
                    default => CalendarEventStatus::Scheduled,
                };

                return new CalendarEvent(
                    id: 'live_session:'.$session->id,
                    source: CalendarEventSource::LiveSession,
                    sourceId: $session->id,
                    kind: CalendarEventKind::LiveSession,
                    status: $status,
                    title: $session->title,
                    startsAt: $startsAt,
                    endsAt: $endsAt,
                    timezone: $query->timezone,
                    classroomId: $session->classroom_id,
                    courseId: $session->classroom?->course_id,
                    ownerId: $session->teacher_id,
                    url: $this->routeFor($query, $session),
                    actions: $query->viewer->role === 'student' ? ['view', 'join'] : ['view', 'edit', 'start', 'cancel'],
                    metadata: [
                        'classroom_name' => $session->classroom?->name,
                        'provider' => $session->provider->value,
                        'live_session_id' => $session->id,
                        'joinable' => in_array($session->status, ['waiting', 'live'], true),
                    ],
                );
            });
    }

    private function routeFor(CalendarQuery $query, LiveSession $session): ?string
    {
        $joinable = in_array($session->status, ['waiting', 'live'], true);
        $route = match (true) {
            $query->viewer->role === 'student' && $joinable => 'student.live-sessions.room',
            $query->viewer->role !== 'student' && $joinable => 'teacher.live-sessions.room',
            $query->viewer->role === 'student' => 'student.live-sessions.index',
            default => 'teacher.live-sessions.index',
        };

        return Route::has($route) ? route($route, $joinable ? $session : ['session' => $session->id]) : null;
    }
}
