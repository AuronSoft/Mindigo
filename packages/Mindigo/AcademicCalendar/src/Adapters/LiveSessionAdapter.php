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
                    url: $this->routeFor($query),
                    actions: $query->viewer->role === 'student' ? ['view', 'join'] : ['view', 'edit', 'start', 'cancel'],
                    metadata: ['classroom_name' => $session->classroom?->name, 'provider' => $session->provider->value],
                );
            });
    }

    private function routeFor(CalendarQuery $query): ?string
    {
        $route = $query->viewer->role === 'student' ? 'student.live-sessions.index' : 'teacher.live-sessions.index';

        return Route::has($route) ? route($route) : null;
    }
}
