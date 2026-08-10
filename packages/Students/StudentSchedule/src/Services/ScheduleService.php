<?php

namespace Mindigo\StudentSchedule\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mindigo\AcademicCalendar\Data\CalendarEvent;
use Mindigo\AcademicCalendar\Data\CalendarQuery;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
use Mindigo\AcademicCalendar\Services\AcademicCalendarService;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;

class ScheduleService
{
    public function __construct(private readonly AcademicCalendarService $calendar) {}

    // ID các lớp học sinh đang tham gia (status active)

    public function classroomIdsForStudent(int|string $studentId): Collection
    {
        return Classroom::query()
            ->whereHas('students', function ($q) use ($studentId) {
                $q->where('student_id', $studentId)
                    ->where('classroom_students.status', 'active');
            })
            ->pluck('id');
    }

    /**
     * Gộp sự kiện từ 4 nguồn trong khoảng [$from, $to].
     * Mỗi sự kiện: (object){ type, title, at(Carbon), end(Carbon|null), url, classroom, tone, icon }
     */
    public function eventsBetween(int|string $studentId, Carbon $from, Carbon $to): Collection
    {
        $student = User::query()->findOrFail($studentId);

        return $this->calendar->events(new CalendarQuery(
            viewer: $student,
            from: $from->toImmutable(),
            to: $to->toImmutable(),
            timezone: config('app.timezone', 'Asia/Ho_Chi_Minh'),
        ))->map(fn (CalendarEvent $event) => $this->legacyEvent($event));
    }

    /**
     * Lưới lịch theo tháng: mảng các tuần, mỗi ngày kèm sự kiện.
     */
    public function buildCalendar(int|string $studentId, Carbon $month): array
    {
        $gridStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $byDay = $this->eventsBetween($studentId, $gridStart, $gridEnd)
            ->groupBy(fn ($e) => $e->at->format('Y-m-d'));

        $weeks = [];
        $cursor = $gridStart->copy();

        while ($cursor <= $gridEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $key = $cursor->format('Y-m-d');
                $week[] = (object) [
                    'date' => $cursor->copy(),
                    'in_month' => $cursor->month === $month->month,
                    'is_today' => $cursor->isToday(),
                    'events' => ($byDay->get($key) ?? collect())->values(),
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    /**
     * Danh sách sự kiện sắp tới (từ bây giờ).
     */
    public function upcoming(int|string $studentId, int $limit = 8): Collection
    {
        return $this->eventsBetween($studentId, now()->startOfDay(), now()->copy()->addDays(45))
            ->filter(fn ($e) => $e->at->gte(now()))
            ->take($limit)
            ->values();
    }

    private function legacyEvent(CalendarEvent $event): object
    {
        [$type, $tone, $icon] = match ($event->kind) {
            CalendarEventKind::ClassSession => ['class', 'indigo', 'heroicon-o-calendar-days'],
            CalendarEventKind::AssignmentDue => ['assignment', 'amber', 'heroicon-o-clipboard-document-list'],
            CalendarEventKind::ExamWindow => ['exam', 'violet', 'heroicon-o-document-text'],
            CalendarEventKind::LiveSession => ['live', 'rose', 'heroicon-o-video-camera'],
        };

        return (object) [
            'type' => $type,
            'title' => $event->title,
            'at' => Carbon::instance($event->startsAt),
            'end' => $event->endsAt ? Carbon::instance($event->endsAt) : null,
            'url' => $event->url,
            'classroom' => $event->metadata['classroom_name'] ?? null,
            'tone' => $tone,
            'icon' => $icon,
        ];
    }
}
