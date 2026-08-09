<?php

namespace Mindigo\StudentSchedule\Services;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherLiveSession\Models\LiveSession;

class ScheduleService
{
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
        $classroomIds = $this->classroomIdsForStudent($studentId);
        $events = collect();

        if ($classroomIds->isEmpty()) {
            // Vẫn còn nguồn đề thi toàn hệ thống bên dưới
            $classroomIds = collect();
        }

        // 1) Buổi học của lớp (classroom_schedules)
        if ($classroomIds->isNotEmpty()) {
            ClassroomSchedule::query()
                ->whereIn('classroom_id', $classroomIds)
                ->whereBetween('session_date', [$from->toDateString(), $to->toDateString()])
                ->with('classroom:id,name')
                ->get()
                ->each(function ($s) use ($events) {
                    $at = $this->combine($s->session_date, $s->start_time);
                    $events->push((object) [
                        'type' => 'class',
                        'title' => $s->title,
                        'at' => $at,
                        'end' => $s->end_time ? $this->combine($s->session_date, $s->end_time) : null,
                        'url' => Route::has('student.classrooms.show') && $s->classroom ? route('student.classrooms.show', $s->classroom_id) : null,
                        'classroom' => $s->classroom?->name,
                        'tone' => 'indigo',
                        'icon' => 'heroicon-o-calendar-days',
                    ]);
                });

            // 2) Hạn nộp bài tập
            Assignment::query()
                ->whereIn('classroom_id', $classroomIds)
                ->where('status', 'published')
                ->whereBetween('due_date', [$from, $to])
                ->with('classroom:id,name')
                ->get()
                ->each(function ($a) use ($events) {
                    $events->push((object) [
                        'type' => 'assignment',
                        'title' => $a->title,
                        'at' => $a->due_date,
                        'end' => null,
                        'url' => Route::has('student.assignments.index') ? route('student.assignments.index') : null,
                        'classroom' => $a->classroom?->name,
                        'tone' => 'amber',
                        'icon' => 'heroicon-o-clipboard-document-list',
                    ]);
                });

            // 4) Buổi học trực tuyến
            LiveSession::query()
                ->whereIn('classroom_id', $classroomIds)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('scheduled_start', [$from, $to])
                ->with('classroom:id,name')
                ->get()
                ->each(function ($l) use ($events) {
                    $events->push((object) [
                        'type' => 'live',
                        'title' => $l->title,
                        'at' => $l->scheduled_start,
                        'end' => $l->scheduled_end,
                        'url' => Route::has('student.live-sessions.index') ? route('student.live-sessions.index') : null,
                        'classroom' => $l->classroom?->name,
                        'tone' => 'rose',
                        'icon' => 'heroicon-o-video-camera',
                    ]);
                });
        }

        // 3) Đề thi mở (toàn hệ thống, audience = student)
        Exam::query()
            ->where('status', 'published')
            ->whereNotNull('starts_at')
            ->whereBetween('starts_at', [$from, $to])
            ->get()
            ->each(function ($e) use ($events) {
                $events->push((object) [
                    'type' => 'exam',
                    'title' => $e->title,
                    'at' => $e->starts_at,
                    'end' => $e->ends_at,
                    'url' => Route::has('student.exams.index') ? route('student.exams.index') : null,
                    'classroom' => null,
                    'tone' => 'violet',
                    'icon' => 'heroicon-o-document-text',
                ]);
            });

        return $events->sortBy('at')->values();
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

    private function combine(CarbonInterface|DateTimeInterface|string $date, ?string $time): Carbon
    {
        $d = $date instanceof DateTimeInterface
            ? $date->format('Y-m-d')
            : Carbon::parse($date)->format('Y-m-d');

        $t = $time ? substr($time, 0, 8) : '00:00:00';

        return Carbon::parse("{$d} {$t}");
    }
}
