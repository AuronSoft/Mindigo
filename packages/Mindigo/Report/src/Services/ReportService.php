<?php

namespace Mindigo\Report\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherAssignment\Models\AssignmentSubmission;

class ReportService
{
    public function getOverviewStats(): array
    {
        $totalAttempts = ExamAttempt::where('status', 'submitted')->count();
        $passedAttempts = ExamAttempt::where('status', 'submitted')->where('passed', true)->count();
        $avgScore = ExamAttempt::where('status', 'submitted')->avg('percentage') ?? 0;

        $prevMonthAttempts = ExamAttempt::where('status', 'submitted')
            ->whereBetween('submitted_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])
            ->count();
        $curMonthAttempts = ExamAttempt::where('status', 'submitted')
            ->where('submitted_at', '>=', now()->startOfMonth())
            ->count();

        return [
            'total_exams' => Exam::count(),
            'published_exams' => Exam::where('status', 'published')->count(),
            'total_students' => User::students()->count(),
            'active_students' => User::students()->active()->count(),
            'total_attempts' => $totalAttempts,
            'passed_attempts' => $passedAttempts,
            'pass_rate' => $totalAttempts > 0 ? round($passedAttempts / $totalAttempts * 100, 1) : 0,
            'avg_score' => round($avgScore, 1),
            'total_questions' => Question::where('status', 'approved')->count(),
            'pending_questions' => Question::where('status', 'reviewing')->count(),
            'cur_month_attempts' => $curMonthAttempts,
            'prev_month_attempts' => $prevMonthAttempts,
            'growth' => $prevMonthAttempts > 0
                ? round(($curMonthAttempts - $prevMonthAttempts) / $prevMonthAttempts * 100, 1)
                : 0,
        ];
    }

    public function getAttemptTrend(int $days = 30): array
    {
        $rows = DB::table('exam_attempts')
            ->where('status', 'submitted')
            ->where('submitted_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(submitted_at) as date, COUNT(*) as count, AVG(percentage) as avg_score')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $counts = [];
        $scores = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d/m');
            $counts[] = $rows[$date]->count ?? 0;
            $scores[] = isset($rows[$date]) ? round($rows[$date]->avg_score, 1) : null;
        }

        return compact('labels', 'counts', 'scores');
    }

    public function getTopExams(int $limit = 10): Collection
    {
        return Exam::whereHas('attempts', fn ($q) => $q->where('status', 'submitted'))
            ->withCount(['attempts' => fn ($q) => $q->where('status', 'submitted')])
            ->withAvg(['attempts' => fn ($q) => $q->where('status', 'submitted')], 'percentage')
            ->orderByDesc('attempts_count')
            ->limit($limit)
            ->get();
    }

    public function getTopStudents(int $limit = 10): Collection
    {
        return DB::table('exam_attempts')
            ->join('users', 'users.id', '=', 'exam_attempts.user_id')
            ->where('exam_attempts.status', 'submitted')
            ->selectRaw('
                users.id, users.name, users.email,
                COUNT(*) as attempt_count,
                ROUND(AVG(exam_attempts.percentage), 1) as avg_score,
                ROUND(AVG(CASE WHEN exam_attempts.passed = 1 THEN 100 ELSE 0 END), 1) as pass_rate,
                MAX(exam_attempts.submitted_at) as last_attempt_at
            ')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('avg_score')
            ->limit($limit)
            ->get();
    }

    public function getSubjectBreakdown(): Collection
    {
        return DB::table('exam_attempts')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->where('exam_attempts.status', 'submitted')
            ->whereNotNull('exams.subject')
            ->where('exams.subject', '!=', '')
            ->selectRaw('
                exams.subject,
                COUNT(*) as attempt_count,
                ROUND(AVG(exam_attempts.percentage), 1) as avg_score,
                ROUND(AVG(CASE WHEN exam_attempts.passed = 1 THEN 100 ELSE 0 END), 1) as pass_rate
            ')
            ->groupBy('exams.subject')
            ->orderByDesc('attempt_count')
            ->get();
    }

    public function getScoreDistribution(): array
    {
        $buckets = [
            '0–20' => [0, 20],
            '20–40' => [20, 40],
            '40–60' => [40, 60],
            '60–80' => [60, 80],
            '80–100' => [80, 101],
        ];

        $distribution = [];
        foreach ($buckets as $label => [$min, $max]) {
            $distribution[$label] = ExamAttempt::where('status', 'submitted')
                ->where('percentage', '>=', $min)
                ->where('percentage', '<', $max)
                ->count();
        }

        return $distribution;
    }

    public function getExamReport(Exam $exam): array
    {
        $attempts = $exam->attempts()->where('status', 'submitted');
        $total = $attempts->count();
        $passed = (clone $attempts)->where('passed', true)->count();
        $avgScore = (clone $attempts)->avg('percentage') ?? 0;
        // Compute avg duration in PHP to stay DB-agnostic (avoid MySQL-only TIMESTAMPDIFF)
        $durRows = (clone $attempts)
            ->whereNotNull('started_at')
            ->whereNotNull('submitted_at')
            ->pluck('submitted_at', 'started_at');
        $avgDuration = $durRows->isNotEmpty()
            ? (int) $durRows->map(fn ($sub, $start) => max(0, (int) round((strtotime($sub) - strtotime($start)) / 60)))->average()
            : 0;

        $distribution = [];
        foreach ([
            '0–20' => [0, 20], '20–40' => [20, 40], '40–60' => [40, 60],
            '60–80' => [60, 80], '80–100' => [80, 101],
        ] as $label => [$min, $max]) {
            $distribution[$label] = (clone $attempts)
                ->where('percentage', '>=', $min)
                ->where('percentage', '<', $max)
                ->count();
        }

        $topCandidates = DB::table('exam_attempts')
            ->join('users', 'users.id', '=', 'exam_attempts.user_id')
            ->where('exam_attempts.exam_id', $exam->id)
            ->where('exam_attempts.status', 'submitted')
            ->select('users.name', 'exam_attempts.percentage', 'exam_attempts.score', 'exam_attempts.passed', 'exam_attempts.submitted_at')
            ->orderByDesc('exam_attempts.percentage')
            ->limit(10)
            ->get();

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $total - $passed,
            'pass_rate' => $total > 0 ? round($passed / $total * 100, 1) : 0,
            'avg_score' => round($avgScore, 1),
            'avg_duration_mins' => round($avgDuration),
            'score_distribution' => $distribution,
            'top_candidates' => $topCandidates,
        ];
    }

    public function getStudentReport(User $student): array
    {
        $attempts = ExamAttempt::where('user_id', $student->id)->where('status', 'submitted');
        $total = $attempts->count();
        $passed = (clone $attempts)->where('passed', true)->count();
        $avgScore = (clone $attempts)->avg('percentage') ?? 0;

        $history = ExamAttempt::where('user_id', $student->id)
            ->where('status', 'submitted')
            ->with('exam:id,title,subject')
            ->orderByDesc('submitted_at')
            ->limit(20)
            ->get();

        $subjectPerf = DB::table('exam_attempts')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->where('exam_attempts.user_id', $student->id)
            ->where('exam_attempts.status', 'submitted')
            ->whereNotNull('exams.subject')
            ->where('exams.subject', '!=', '')
            ->selectRaw('exams.subject, COUNT(*) as count, ROUND(AVG(exam_attempts.percentage), 1) as avg_score')
            ->groupBy('exams.subject')
            ->orderByDesc('avg_score')
            ->get();

        return [
            'total_attempts' => $total,
            'passed' => $passed,
            'pass_rate' => $total > 0 ? round($passed / $total * 100, 1) : 0,
            'avg_score' => round($avgScore, 1),
            'exam_history' => $history,
            'subject_performance' => $subjectPerf,
        ];
    }

    public function getAllExams(int $perPage = 15): LengthAwarePaginator
    {
        return Exam::withCount(['attempts' => fn ($q) => $q->where('status', 'submitted')])
            ->withAvg(['attempts' => fn ($q) => $q->where('status', 'submitted')], 'percentage')
            ->latest()
            ->paginate($perPage);
    }

    public function getAllStudents(int $perPage = 15): LengthAwarePaginator
    {
        return User::students()
            ->select('users.*')
            ->selectSub(
                ExamAttempt::where('status', 'submitted')
                    ->whereColumn('user_id', 'users.id')
                    ->selectRaw('COUNT(*)'),
                'attempts_count'
            )
            ->selectSub(
                ExamAttempt::where('status', 'submitted')
                    ->whereColumn('user_id', 'users.id')
                    ->selectRaw('ROUND(AVG(percentage), 1)'),
                'attempts_avg_percentage'
            )
            ->orderByDesc('attempts_count')
            ->paginate($perPage);
    }

    public function getTeacherClassrooms(User $teacher): Collection
    {
        return Classroom::query()
            ->where('teacher_id', $teacher->getAuthIdentifier())
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }

    public function getTeacherReport(User $teacher, ?Classroom $classroom = null, int $days = 30): array
    {
        $studentIds = $this->teacherStudentIds($teacher, $classroom);
        $attempts = $this->teacherAttempts($teacher, $studentIds)
            ->where('submitted_at', '>=', now()->subDays($days - 1)->startOfDay());

        $totalAttempts = (clone $attempts)->count();
        $passedAttempts = (clone $attempts)->where('passed', true)->count();
        $avgScore = ((clone $attempts)->avg('percentage') ?? 0) / 10;

        $assignmentQuery = Assignment::query()->where('teacher_id', $teacher->getAuthIdentifier());
        if ($classroom) {
            $assignmentQuery->where('classroom_id', $classroom->id);
        }

        $assignmentIds = (clone $assignmentQuery)->pluck('id');
        $submissionQuery = AssignmentSubmission::query()->whereIn('assignment_id', $assignmentIds);

        $totalSubmissions = (clone $submissionQuery)->count();
        $gradedSubmissions = (clone $submissionQuery)->whereNotNull('score')->count();

        return [
            'summary' => [
                'total_students' => $studentIds->count(),
                'total_exams' => $this->teacherExamQuery($teacher, $classroom, $studentIds)->count(),
                'total_attempts' => $totalAttempts,
                'pass_rate' => $totalAttempts > 0 ? round($passedAttempts / $totalAttempts * 100, 1) : 0,
                'avg_score' => round($avgScore, 1),
                'total_assignments' => (clone $assignmentQuery)->count(),
                'submission_rate' => $studentIds->count() > 0 && (clone $assignmentQuery)->count() > 0
                    ? round($totalSubmissions / ($studentIds->count() * (clone $assignmentQuery)->count()) * 100, 1)
                    : 0,
                'graded_submissions' => $gradedSubmissions,
            ],
            'trend' => $this->teacherAttemptTrend($teacher, $studentIds, $days),
            'score_distribution' => $this->teacherScoreDistribution($teacher, $studentIds, $days),
            'classrooms' => $this->teacherClassroomPerformance($teacher, $days),
            'exams' => $this->teacherExamPerformance($teacher, $classroom, $studentIds, $days),
            'students' => $this->teacherStudentAttention($teacher, $studentIds, $days),
        ];
    }

    private function teacherStudentIds(User $teacher, ?Classroom $classroom = null): Collection
    {
        $query = DB::table('classroom_students')
            ->join('classrooms', 'classrooms.id', '=', 'classroom_students.classroom_id')
            ->where('classrooms.teacher_id', $teacher->getAuthIdentifier())
            ->whereNull('classrooms.deleted_at');

        if ($classroom) {
            $query->where('classroom_students.classroom_id', $classroom->id);
        }

        return $query->pluck('classroom_students.student_id')->unique()->values();
    }

    private function teacherAttempts(User $teacher, Collection $studentIds): Builder
    {
        return ExamAttempt::query()
            ->whereHas('exam', fn (Builder $query) => $query->where('created_by', $teacher->getAuthIdentifier()))
            ->whereIn('user_id', $studentIds)
            ->where('status', 'submitted');
    }

    private function teacherExamQuery(User $teacher, ?Classroom $classroom, Collection $studentIds): Builder
    {
        $query = Exam::query()->where('created_by', $teacher->getAuthIdentifier());

        if ($classroom) {
            $query->whereHas('attempts', fn (Builder $attempts) => $attempts->whereIn('user_id', $studentIds));
        }

        return $query;
    }

    private function teacherAttemptTrend(User $teacher, Collection $studentIds, int $days): array
    {
        $rows = $this->teacherAttempts($teacher, $studentIds)
            ->where('submitted_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->selectRaw('DATE(submitted_at) as date, COUNT(*) as count, ROUND(AVG(percentage) / 10, 1) as avg_score')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $counts = [];
        $scores = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $date = $day->toDateString();
            $labels[] = $day->format('d/m');
            $counts[] = $rows[$date]->count ?? 0;
            $scores[] = isset($rows[$date]) ? (float) $rows[$date]->avg_score : null;
        }

        return compact('labels', 'counts', 'scores');
    }

    private function teacherScoreDistribution(User $teacher, Collection $studentIds, int $days): array
    {
        $attempts = $this->teacherAttempts($teacher, $studentIds)
            ->where('submitted_at', '>=', now()->subDays($days - 1)->startOfDay());

        $distribution = [];
        foreach (['0-2' => [0, 20], '2-4' => [20, 40], '4-6' => [40, 60], '6-8' => [60, 80], '8-10' => [80, 101]] as $label => [$min, $max]) {
            $distribution[$label] = (clone $attempts)
                ->where('percentage', '>=', $min)
                ->where('percentage', '<', $max)
                ->count();
        }

        return $distribution;
    }

    private function teacherClassroomPerformance(User $teacher, int $days): Collection
    {
        return $this->getTeacherClassrooms($teacher)->map(function (Classroom $classroom) use ($teacher, $days) {
            $studentIds = $this->teacherStudentIds($teacher, $classroom);
            $attempts = $this->teacherAttempts($teacher, $studentIds)
                ->where('submitted_at', '>=', now()->subDays($days - 1)->startOfDay());
            $total = (clone $attempts)->count();
            $passed = (clone $attempts)->where('passed', true)->count();
            $assignmentIds = Assignment::query()
                ->where('teacher_id', $teacher->getAuthIdentifier())
                ->where('classroom_id', $classroom->id)
                ->pluck('id');

            return [
                'classroom' => $classroom,
                'students' => $studentIds->count(),
                'attempts' => $total,
                'avg_score' => round(((clone $attempts)->avg('percentage') ?? 0) / 10, 1),
                'pass_rate' => $total > 0 ? round($passed / $total * 100, 1) : 0,
                'assignments' => $assignmentIds->count(),
                'submissions' => AssignmentSubmission::query()->whereIn('assignment_id', $assignmentIds)->count(),
            ];
        });
    }

    private function teacherExamPerformance(User $teacher, ?Classroom $classroom, Collection $studentIds, int $days): Collection
    {
        $attemptFilter = function (Builder $query) use ($studentIds, $days): void {
            $query->where('status', 'submitted')
                ->whereIn('user_id', $studentIds)
                ->where('submitted_at', '>=', now()->subDays($days - 1)->startOfDay());
        };

        return Exam::query()
            ->where('created_by', $teacher->getAuthIdentifier())
            ->when($classroom, fn (Builder $query) => $query->whereHas('attempts', fn (Builder $attempts) => $attempts->whereIn('user_id', $studentIds)))
            ->withCount(['attempts' => $attemptFilter])
            ->withAvg(['attempts' => $attemptFilter], 'percentage')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(function (Exam $exam) use ($studentIds, $days) {
                $attempts = ExamAttempt::query()
                    ->where('exam_id', $exam->id)
                    ->where('status', 'submitted')
                    ->whereIn('user_id', $studentIds)
                    ->where('submitted_at', '>=', now()->subDays($days - 1)->startOfDay());
                $total = (clone $attempts)->count();
                $passed = (clone $attempts)->where('passed', true)->count();

                return [
                    'exam' => $exam,
                    'attempts' => $total,
                    'avg_score' => round(($exam->attempts_avg_percentage ?? 0) / 10, 1),
                    'pass_rate' => $total > 0 ? round($passed / $total * 100, 1) : 0,
                    'last_submitted_at' => (clone $attempts)->max('submitted_at'),
                ];
            });
    }

    private function teacherStudentAttention(User $teacher, Collection $studentIds, int $days): Collection
    {
        return User::students()
            ->whereIn('id', $studentIds)
            ->select('id', 'name', 'email')
            ->get()
            ->map(function (User $student) use ($teacher, $days) {
                $attempts = ExamAttempt::query()
                    ->where('user_id', $student->id)
                    ->whereHas('exam', fn (Builder $query) => $query->where('created_by', $teacher->getAuthIdentifier()))
                    ->where('status', 'submitted')
                    ->where('submitted_at', '>=', now()->subDays($days - 1)->startOfDay());
                $total = (clone $attempts)->count();
                $passed = (clone $attempts)->where('passed', true)->count();

                return [
                    'student' => $student,
                    'attempts' => $total,
                    'avg_score' => round(((clone $attempts)->avg('percentage') ?? 0) / 10, 1),
                    'pass_rate' => $total > 0 ? round($passed / $total * 100, 1) : 0,
                    'last_at' => (clone $attempts)->max('submitted_at'),
                ];
            })
            ->sortBy(fn (array $row) => [$row['attempts'] > 0 ? 1 : 0, $row['avg_score'], $row['pass_rate']])
            ->values()
            ->take(8);
    }
}
