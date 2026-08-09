<?php

namespace Mindigo\StudentDashboard\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherAssignment\Models\AssignmentSubmission;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherCourse\Models\CourseEnrollment;

class DashboardService
{
    public function activeCourses(User $student): Collection
    {
        return CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('status', CourseEnrollment::ACTIVE_STATUSES)
            ->availableToStudent()
            ->with('course:id,name,slug,cover_image')
            ->latest('last_activity_at')
            ->take(3)
            ->get();
    }

    /**
     * ID các lớp mà học sinh đang tham gia.
     */
    public function classroomIds(User $student): Collection
    {
        return Classroom::query()
            ->whereHas('students', fn ($q) => $q->where('student_id', $student->id))
            ->pluck('id');
    }

    /**
     * Submission của học sinh, key theo assignment_id.
     */
    protected function submissions(User $student): Collection
    {
        return AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->get()
            ->keyBy('assignment_id');
    }

    /**
     * 4 thẻ thống kê đầu trang (đều có %, tỉ lệ x/y và thanh tiến độ).
     */
    public function getStudyStats(User $student, Collection $classroomIds): array
    {
        $submissions = $this->submissions($student);

        // Bài tập đã nộp
        $assignmentTotal = $classroomIds->isEmpty() ? 0 : Assignment::query()
            ->whereIn('classroom_id', $classroomIds)
            ->where('status', 'published')
            ->count();
        $assignmentDone = $submissions->count();

        // Bài thi đã làm / tổng đề đã xuất bản
        $examTotal = Exam::query()->where('status', 'published')->count();
        $examDone = ExamAttempt::query()
            ->where('user_id', $student->id)
            ->whereNotNull('submitted_at')
            ->distinct('exam_id')
            ->count('exam_id');

        // Điểm trung bình
        $avgScore = (float) (ExamAttempt::query()
            ->where('user_id', $student->id)
            ->whereNotNull('submitted_at')
            ->avg('percentage') ?? 0);

        return [
            'assignments' => [
                'percent' => $this->percent($assignmentDone, $assignmentTotal),
                'done' => $assignmentDone,
                'total' => $assignmentTotal,
            ],
            'exams' => [
                'percent' => $this->percent($examDone, $examTotal),
                'done' => $examDone,
                'total' => $examTotal,
            ],
            'weekly' => [
                'percent' => $this->getWeeklyProgress($student, $classroomIds),
            ],
            'avg_score' => [
                'percent' => (int) round($avgScore),
            ],
        ];
    }

    /**
     * Tiến độ tuần này (% bài tập đến hạn trong tuần đã nộp).
     */
    public function getWeeklyProgress(User $student, Collection $classroomIds): int
    {
        if ($classroomIds->isEmpty()) {
            return 0;
        }

        $weekAssignments = Assignment::query()
            ->whereIn('classroom_id', $classroomIds)
            ->where('status', 'published')
            ->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->pluck('id');

        if ($weekAssignments->isEmpty()) {
            return 0;
        }

        $done = AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->whereIn('assignment_id', $weekAssignments)
            ->count();

        return $this->percent($done, $weekAssignments->count());
    }

    /**
     * Dải ngày của tuần hiện tại cho bộ chọn lịch.
     */
    public function getWeekStrip(): array
    {
        $cursor = now()->startOfWeek();
        $today = now()->startOfDay();
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $days[] = [
                'day' => $cursor->day,
                'label' => $cursor->translatedFormat('D'),
                'is_today' => $cursor->isSameDay($today),
            ];
            $cursor->addDay();
        }

        return $days;
    }

    /**
     * Sáu tuần của tháng hiện tại, bắt đầu từ thứ Hai.
     */
    public function getMonthCalendar(Collection $tasks): array
    {
        $month = now()->startOfMonth();
        $cursor = $month->copy()->startOfWeek(Carbon::MONDAY);
        $taskCounts = $tasks->filter(fn ($task) => $task->at)->countBy(fn ($task) => $task->at->toDateString());
        $weeks = [];

        for ($week = 0; $week < 6; $week++) {
            $days = [];
            for ($day = 0; $day < 7; $day++) {
                $days[] = [
                    'date' => $cursor->toDateString(),
                    'day' => $cursor->day,
                    'is_current_month' => $cursor->month === $month->month,
                    'is_today' => $cursor->isToday(),
                    'task_count' => $taskCounts->get($cursor->toDateString(), 0),
                ];
                $cursor->addDay();
            }
            $weeks[] = $days;
        }

        return $weeks;
    }

    /**
     * Việc cần làm sắp tới (bài tập chưa nộp + đề thi sắp đóng), kèm thời gian còn lại.
     */
    public function getActiveTasks(User $student, Collection $classroomIds): Collection
    {
        $tasks = collect();

        if ($classroomIds->isNotEmpty()) {
            $submittedIds = AssignmentSubmission::query()
                ->where('student_id', $student->id)
                ->pluck('assignment_id');

            Assignment::query()
                ->whereIn('classroom_id', $classroomIds)
                ->where('status', 'published')
                ->whereNotIn('id', $submittedIds)
                ->where('due_date', '>=', now())
                ->with('classroom')
                ->orderBy('due_date')
                ->take(5)
                ->get()
                ->each(fn (Assignment $a) => $tasks->push((object) [
                    'type' => 'assignment',
                    'title' => $a->title,
                    'status' => __('student-dashboard::app.status_not_submitted'),
                    'at' => $a->due_date,
                    'time_left' => $this->timeLeft($a->due_date),
                ]));
        }

        Exam::query()
            ->where('status', 'published')
            ->whereNotNull('ends_at')
            ->where('ends_at', '>=', now())
            ->orderBy('ends_at')
            ->take(5)
            ->get()
            ->each(fn (Exam $e) => $tasks->push((object) [
                'type' => 'exam',
                'title' => $e->title,
                'status' => __('student-dashboard::app.status_not_started'),
                'at' => $e->ends_at,
                'time_left' => $this->timeLeft($e->ends_at),
            ]));

        return $tasks->sortBy('at')->take(4)->values();
    }

    /**
     * Thống kê tiến độ bài tập cho biểu đồ donut.
     */
    public function getCourseStats(User $student, Collection $classroomIds): array
    {
        $total = $classroomIds->isEmpty() ? 0 : Assignment::query()
            ->whereIn('classroom_id', $classroomIds)
            ->where('status', 'published')
            ->count();

        $submissions = $this->submissions($student);
        $completed = $submissions->whereIn('status', ['graded', 'returned'])->count();
        $inProgress = $submissions->where('status', 'submitted')->count();
        $incomplete = max(0, $total - $completed - $inProgress);

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'incomplete' => $incomplete,
            'pct_completed' => $this->percent($completed, $total),
            'pct_in_progress' => $this->percent($inProgress, $total),
            'pct_incomplete' => $this->percent($incomplete, $total),
        ];
    }

    /**
     * Số hoạt động học tập theo từng ngày trong tuần (cho biểu đồ cột).
     */
    public function getWeeklyActivity(User $student): array
    {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek();

        $counts = array_fill(1, 7, 0); // 1=Mon .. 7=Sun

        AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->whereBetween('submitted_at', [$start, $end])
            ->pluck('submitted_at')
            ->each(function ($d) use (&$counts) {
                $counts[Carbon::parse($d)->dayOfWeekIso]++;
            });

        ExamAttempt::query()
            ->where('user_id', $student->id)
            ->whereBetween('submitted_at', [$start, $end])
            ->pluck('submitted_at')
            ->each(function ($d) use (&$counts) {
                $counts[Carbon::parse($d)->dayOfWeekIso]++;
            });

        return array_values($counts);
    }

    /**
     * Hoạt động gần đây của học sinh (nộp bài / làm thi).
     */
    public function getRecentActivity(User $student): Collection
    {
        $items = collect();

        AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->with('assignment')
            ->latest('submitted_at')
            ->take(5)
            ->get()
            ->each(fn ($s) => $items->push((object) [
                'type' => 'assignment',
                'text' => $s->assignment?->title,
                'action' => __('student-dashboard::app.act_submitted'),
                'at' => $s->submitted_at,
            ]));

        ExamAttempt::query()
            ->where('user_id', $student->id)
            ->whereNotNull('submitted_at')
            ->with('exam')
            ->latest('submitted_at')
            ->take(5)
            ->get()
            ->each(fn ($a) => $items->push((object) [
                'type' => 'exam',
                'text' => $a->exam?->title,
                'action' => __('student-dashboard::app.act_finished_exam'),
                'at' => $a->submitted_at,
            ]));

        return $items->sortByDesc('at')->take(5)->values();
    }

    // ----- helpers -----

    protected function percent(int $value, int $total): int
    {
        return $total > 0 ? (int) round($value / $total * 100) : 0;
    }

    protected function timeLeft(?Carbon $when): string
    {
        if (! $when) {
            return '';
        }

        $hours = now()->diffInHours($when, false);

        if ($hours < 0) {
            return __('student-dashboard::app.overdue');
        }

        if ($hours < 24) {
            return __('student-dashboard::app.time_left_hours', ['count' => max(1, (int) round($hours))]);
        }

        return __('student-dashboard::app.time_left_days', ['count' => (int) floor($hours / 24)]);
    }
}
