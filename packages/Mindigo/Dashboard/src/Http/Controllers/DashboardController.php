<?php

namespace Mindigo\Dashboard\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\QuestionBank\Models\Question;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'students' => User::students()->count(),
            'teachers' => User::teachers()->count(),
            'admins' => User::admins()->count(),
            'active_users' => User::active()->count(),
        ];

        $totalExams = Exam::count();
        $recentExams = Exam::where('created_at', '>=', now()->subDays(7))->count();

        $totalAttempts = ExamAttempt::where('status', 'submitted')->count();
        $previousMonthAttempts = ExamAttempt::where('status', 'submitted')
            ->where('submitted_at', '<', now()->subMonth()->startOfMonth())
            ->count();

        $currentMonthAttempts = ExamAttempt::where('status', 'submitted')
            ->where('submitted_at', '>=', now()->startOfMonth())
            ->count();
        $lastMonthAttempts = ExamAttempt::where('status', 'submitted')
            ->whereBetween('submitted_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])
            ->count();
        $growth = $lastMonthAttempts > 0
            ? round(($currentMonthAttempts - $lastMonthAttempts) / $lastMonthAttempts * 100, 1)
            : 0;

        $passedAttempts = ExamAttempt::where('status', 'submitted')->where('passed', true)->count();
        $passRate = $totalAttempts > 0 ? round($passedAttempts / $totalAttempts * 100) : 0;

        $topPerformer = DB::table('exam_attempts')
            ->join('users', 'users.id', '=', 'exam_attempts.user_id')
            ->where('exam_attempts.status', 'submitted')
            ->selectRaw('users.name, users.id, ROUND(AVG(exam_attempts.percentage), 1) as avg_score, COUNT(*) as attempt_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('avg_score')
            ->first();

        $bestExam = Exam::withCount(['attempts' => fn ($q) => $q->where('status', 'submitted')])
            ->withAvg(['attempts' => fn ($q) => $q->where('status', 'submitted')], 'percentage')
            ->having('attempts_count', '>', 0)
            ->orderByDesc('attempts_avg_percentage')
            ->first();

        $latestExams = Exam::withCount(['attempts' => fn ($q) => $q->where('status', 'submitted')])
            ->withAvg(['attempts' => fn ($q) => $q->where('status', 'submitted')], 'score')
            ->latest()
            ->limit(5)
            ->get();

        $topPerformers = DB::table('exam_attempts')
            ->join('users', 'users.id', '=', 'exam_attempts.user_id')
            ->where('exam_attempts.status', 'submitted')
            ->selectRaw('users.name, ROUND(AVG(exam_attempts.percentage), 1) as avg_score, COUNT(*) as attempt_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('avg_score')
            ->limit(5)
            ->get();

        $totalQuestions = Question::where('status', 'approved')->count();
        $pendingQuestions = Question::where('status', 'reviewing')->count();

        $pendingReview = $pendingQuestions;
        $pendingPublish = Exam::where('status', 'reviewing')->count();
        $openSupport = DB::table('support_tickets')->where('status', 'open')->count();

        $topSubjects = DB::table('exam_attempts')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->where('exam_attempts.status', 'submitted')
            ->whereNotNull('exams.subject')
            ->where('exams.subject', '!=', '')
            ->selectRaw('exams.subject, COUNT(*) as attempt_count')
            ->groupBy('exams.subject')
            ->orderByDesc('attempt_count')
            ->limit(4)
            ->get();

        $totalSubjectAttempts = $topSubjects->sum('attempt_count') ?: 1;

        return view('Mindigo-dashboard::dashboard', compact(
            'stats',
            'totalExams', 'recentExams',
            'totalAttempts', 'previousMonthAttempts',
            'currentMonthAttempts', 'lastMonthAttempts', 'growth',
            'passRate', 'passedAttempts',
            'topPerformer', 'bestExam',
            'latestExams', 'topPerformers',
            'totalQuestions', 'pendingQuestions',
            'pendingReview', 'pendingPublish', 'openSupport',
            'topSubjects', 'totalSubjectAttempts'
        ));
    }
}
