<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;

class ExamFoundationController extends Controller
{
    public function __invoke(): View
    {
        $teacherId = (int) auth()->id();
        $now = CarbonImmutable::now();
        $monthStart = $now->startOfMonth()->subMonths(11);

        $sessionIds = ExamSession::query()
            ->where('organizer_id', $teacherId)
            ->pluck('id');

        $attempts = ExamSessionAttempt::query()
            ->whereIn('exam_session_id', $sessionIds);

        $submittedAttempts = (clone $attempts)->whereNotNull('submitted_at');
        $gradedAttempts = (clone $submittedAttempts)->whereNotNull('percentage');
        $averageScore = round((float) (clone $gradedAttempts)->avg('percentage'), 1);
        $passRate = (clone $gradedAttempts)->count() > 0
            ? round((clone $gradedAttempts)->where('passed', true)->count() / (clone $gradedAttempts)->count() * 100, 1)
            : 0.0;

        $monthlyCounts = (clone $attempts)
            ->where('started_at', '>=', $monthStart)
            ->get(['started_at'])
            ->countBy(fn (ExamSessionAttempt $attempt): string => $attempt->started_at->format('Y-m'));

        $monthlyActivity = collect(range(0, 11))->map(function (int $offset) use ($monthStart, $monthlyCounts): array {
            $month = $monthStart->addMonths($offset);

            return [
                'label' => $month->translatedFormat('M'),
                'count' => (int) $monthlyCounts->get($month->format('Y-m'), 0),
            ];
        });

        $difficulty = ExamTemplateQuestion::query()
            ->join('exam_template_versions', 'exam_template_versions.id', '=', 'exam_template_questions.exam_template_version_id')
            ->join('exam_templates', 'exam_templates.id', '=', 'exam_template_versions.exam_template_id')
            ->where('exam_templates.owner_id', $teacherId)
            ->selectRaw("COALESCE(exam_template_questions.difficulty, 'medium') as level, COUNT(*) as aggregate")
            ->groupBy('level')
            ->pluck('aggregate', 'level');

        $recentAttempts = (clone $attempts)
            ->with(['candidate:id,name,student_code', 'user:id,name,email', 'session:id,title'])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return view('Mindigo-exam-management::foundation', [
            'metrics' => [
                'templates' => ExamTemplate::query()->where('owner_id', $teacherId)->count(),
                'activeStudents' => (clone $attempts)->whereIn('status', [ExamSessionAttempt::STATUS_IN_PROGRESS, ExamSessionAttempt::STATUS_PAUSED])->distinct('user_id')->count('user_id'),
                'questions' => (int) $difficulty->sum(),
                'pendingGrading' => (clone $submittedAttempts)->where('needs_review', true)->count(),
                'averageScore' => $averageScore,
                'passRate' => $passRate,
            ],
            'monthlyActivity' => $monthlyActivity,
            'monthlyMaximum' => max(1, (int) $monthlyActivity->max('count')),
            'difficulty' => collect(['easy', 'medium', 'hard'])->mapWithKeys(fn (string $level): array => [$level => (int) $difficulty->get($level, 0)]),
            'recentAttempts' => $recentAttempts,
        ]);
    }
}
