<?php

namespace Mindigo\Report\Services;

use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\QuestionBank\Models\Question;

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

    public function getTopExams(int $limit = 10): \Illuminate\Support\Collection
    {
        return Exam::whereHas('attempts', fn ($q) => $q->where('status', 'submitted'))
            ->withCount(['attempts' => fn ($q) => $q->where('status', 'submitted')])
            ->withAvg(['attempts' => fn ($q) => $q->where('status', 'submitted')], 'percentage')
            ->orderByDesc('attempts_count')
            ->limit($limit)
            ->get();
    }

    public function getTopStudents(int $limit = 10): \Illuminate\Support\Collection
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

    public function getSubjectBreakdown(): \Illuminate\Support\Collection
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

    public function getAllExams(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Exam::withCount(['attempts' => fn ($q) => $q->where('status', 'submitted')])
            ->withAvg(['attempts' => fn ($q) => $q->where('status', 'submitted')], 'percentage')
            ->latest()
            ->paginate($perPage);
    }

    public function getAllStudents(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
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
}
