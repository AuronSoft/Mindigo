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
use Mindigo\ExamManagement\Models\Exam;

final class ExamAdapter implements CalendarSourceAdapter
{
    public function __construct(private readonly CalendarScope $scope) {}

    public function events(CalendarQuery $query): Collection
    {
        if (! $query->includes(CalendarEventKind::ExamWindow)) {
            return collect();
        }

        $classroomIds = $this->scope->classroomIds($query)->all();

        return Exam::query()
            ->whereNotNull('starts_at')
            ->where('starts_at', '<', $query->to->utc())
            ->where(fn ($builder) => $builder->whereNull('ends_at')->orWhere('ends_at', '>=', $query->from->utc()))
            ->when($query->viewer->role === 'teacher', fn ($builder) => $builder->where('created_by', $query->viewer->id))
            ->when($query->viewer->role === 'student', fn ($builder) => $builder->where('status', 'published'))
            ->get()
            ->filter(fn (Exam $exam) => $query->viewer->role === 'admin' || $this->isInAudience($exam, $classroomIds))
            ->map(function (Exam $exam) use ($query, $classroomIds): CalendarEvent {
                $startsAt = CarbonImmutable::instance($exam->starts_at)->setTimezone($query->timezone);
                $endsAt = $exam->ends_at ? CarbonImmutable::instance($exam->ends_at)->setTimezone($query->timezone) : null;
                $assignedClassrooms = array_values(array_intersect($this->audienceClassrooms($exam), $classroomIds));
                $status = match ($exam->status) {
                    'draft', 'reviewing' => CalendarEventStatus::Draft,
                    'closed' => CalendarEventStatus::Completed,
                    default => $endsAt && $endsAt->isPast() ? CalendarEventStatus::Completed : CalendarEventStatus::Scheduled,
                };

                return new CalendarEvent(
                    id: 'exam:'.$exam->id,
                    source: CalendarEventSource::Exam,
                    sourceId: $exam->id,
                    kind: CalendarEventKind::ExamWindow,
                    status: $status,
                    title: $exam->title,
                    startsAt: $startsAt,
                    endsAt: $endsAt,
                    timezone: $query->timezone,
                    classroomId: count($assignedClassrooms) === 1 ? $assignedClassrooms[0] : null,
                    ownerId: $exam->created_by,
                    url: $this->routeFor($query),
                    actions: $query->viewer->role === 'student' ? ['view', 'take'] : ['view', 'edit'],
                    metadata: ['classroom_ids' => $assignedClassrooms, 'duration_minutes' => $exam->duration_minutes],
                );
            })
            ->values();
    }

    /** @param list<int> $classroomIds */
    private function isInAudience(Exam $exam, array $classroomIds): bool
    {
        return array_intersect($this->audienceClassrooms($exam), $classroomIds) !== [];
    }

    /** @return list<int> */
    private function audienceClassrooms(Exam $exam): array
    {
        return array_values(array_unique(array_map('intval', $exam->audience['classrooms'] ?? [])));
    }

    private function routeFor(CalendarQuery $query): ?string
    {
        $route = $query->viewer->role === 'student' ? 'student.exams.index' : 'teacher.exams.index';

        return Route::has($route) ? route($route) : null;
    }
}
