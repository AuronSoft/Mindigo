<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamTemplate;

class ExamConvergenceService
{
    public function __construct(private readonly ExamCutoverService $cutover) {}

    public function enabled(?User $user = null): bool
    {
        return $this->cutover->mode() === ExamCutoverService::MODE_NEW || $this->cutover->prefersNew($user);
    }

    public function adminDashboard(): array
    {
        $completed = ExamSessionAttempt::query()->whereIn('status', [ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED, ExamSessionAttempt::STATUS_TERMINATED]);
        $totalAttempts = (clone $completed)->count();
        $currentMonth = (clone $completed)->where('submitted_at', '>=', now()->startOfMonth())->count();
        $lastMonth = (clone $completed)->whereBetween('submitted_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count();
        $latest = ExamSession::query()->withCount(['attempts' => fn ($query) => $query->whereNotNull('submitted_at')])
            ->withAvg(['attempts' => fn ($query) => $query->whereNotNull('submitted_at')], 'score')->latest()->limit(5)->get();
        $best = ExamSession::query()->whereHas('attempts', fn ($query) => $query->whereNotNull('submitted_at'))
            ->withCount(['attempts' => fn ($query) => $query->whereNotNull('submitted_at')])
            ->withAvg(['attempts' => fn ($query) => $query->whereNotNull('submitted_at')], 'percentage')
            ->orderByDesc('attempts_avg_percentage')->first();
        $subjects = DB::table('exam_session_attempts')
            ->join('exam_sessions', 'exam_sessions.id', '=', 'exam_session_attempts.exam_session_id')
            ->join('exam_template_versions', 'exam_template_versions.id', '=', 'exam_sessions.exam_template_version_id')
            ->join('exam_templates', 'exam_templates.id', '=', 'exam_template_versions.exam_template_id')
            ->whereNotNull('exam_session_attempts.submitted_at')->whereNotNull('exam_templates.subject')->where('exam_templates.subject', '!=', '')
            ->selectRaw('exam_templates.subject, COUNT(*) as attempt_count')->groupBy('exam_templates.subject')->orderByDesc('attempt_count')->limit(4)->get();

        return [
            'totalExams' => ExamSession::query()->count(),
            'recentExams' => ExamSession::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'totalAttempts' => $totalAttempts,
            'previousMonthAttempts' => (clone $completed)->where('submitted_at', '<', now()->subMonth()->startOfMonth())->count(),
            'currentMonthAttempts' => $currentMonth,
            'lastMonthAttempts' => $lastMonth,
            'growth' => $lastMonth > 0 ? round(($currentMonth - $lastMonth) / $lastMonth * 100, 1) : 0,
            'passedAttempts' => (clone $completed)->where('passed', true)->count(),
            'passRate' => $totalAttempts > 0 ? round((clone $completed)->where('passed', true)->count() / $totalAttempts * 100) : 0,
            // Admin analytics stay operational and aggregate-only. Individual
            // learner performance belongs to the owning teacher or tutor.
            'topPerformer' => null,
            'bestExam' => $best,
            'latestExams' => $latest,
            'topPerformers' => collect(),
            'pendingPublish' => ExamTemplate::query()->where('status', ExamTemplate::STATUS_DRAFT)->count(),
            'topSubjects' => $subjects,
            'totalSubjectAttempts' => $subjects->sum('attempt_count') ?: 1,
        ];
    }

    public function studentAttempts(int $studentId): Collection
    {
        return ExamSessionAttempt::query()->where('user_id', $studentId)->whereNotNull('submitted_at')->get();
    }

    public function studentAvailableSessionCount(int $studentId): int
    {
        return ExamSession::query()->whereHas('candidates', fn ($query) => $query->where('user_id', $studentId))->count();
    }

    public function examPercentages(array $userIds): Collection
    {
        return ExamSessionAttempt::query()->whereIn('user_id', $userIds)->whereNotNull('submitted_at')->whereNotNull('percentage')->get(['user_id', 'percentage']);
    }

    public function searchSessions(string $keyword): Collection
    {
        return ExamSession::query()->where('title', 'like', "%{$keyword}%")->latest()->limit(5)->get(['id', 'title', 'status']);
    }
}
