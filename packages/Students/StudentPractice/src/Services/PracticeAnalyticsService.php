<?php

namespace Mindigo\StudentPractice\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\StudentPractice\Models\StudentSkillProgress;

class PracticeAnalyticsService
{
    public function __construct(
        private readonly PracticeLearningInsightService $insights,
        private readonly PracticeRecommendationService $recommendations,
    ) {}

    public function dashboard(User $student, array $filters): array
    {
        $period = $this->period($filters['period'] ?? '30');
        $skillId = isset($filters['skill_id']) ? (int) $filters['skill_id'] : null;
        $query = $this->attemptQuery($student, $period['start'], $skillId);
        $summary = (clone $query)->selectRaw('COUNT(*) attempts, COALESCE(SUM(total_questions), 0) questions, COALESCE(SUM(correct_answers), 0) correct_count, COALESCE(AVG(score), 0) average_score')->firstOrFail();
        $practiceSeconds = (clone $query)->cursor()->sum(
            fn (PracticeAttempt $attempt): int => $attempt->completed_at?->diffInSeconds($attempt->started_at) ?? 0
        );
        $questions = (int) $summary->questions;
        $overview = [
            'attempts' => (int) $summary->attempts,
            'questions' => $questions,
            'correct' => (int) $summary->correct_count,
            'accuracy' => $questions > 0 ? round(((int) $summary->correct_count / $questions) * 100, 2) : 0,
            'average_score' => round((float) $summary->average_score, 2),
            'practice_seconds' => $practiceSeconds,
        ];
        $trend = (clone $query)->selectRaw('DATE(completed_at) practice_date, COUNT(*) attempts, SUM(total_questions) questions, SUM(correct_answers) correct_count, AVG(score) average_score')
            ->groupByRaw('DATE(completed_at)')->orderBy('practice_date')->get();
        $skills = $this->skillAnalytics($student, $period['start'], $skillId);
        $improvement = $this->improvement($trend);
        $analytics = [
            'scope' => $skillId ? 'skill-'.$skillId : 'all',
            'period' => $period,
            'overview' => $overview,
            'trend' => $trend,
            'skills' => $skills,
            'strength' => $skills->sortByDesc('mastery_score')->first(),
            'weakness' => $skills->sortBy('mastery_score')->first(),
            'improvement' => $improvement,
        ];

        return [
            ...$analytics,
            'insights' => $this->insights->synchronize($student, $analytics),
            'recommendations' => $this->recommendations->feed($student),
            'history' => (clone $query)->with('practiceSkill:id,name')->latest('completed_at')->paginate(12)->withQueryString(),
            'skillOptions' => PracticeSkill::query()->where('status', PracticeSkill::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function attemptQuery(User $student, ?CarbonImmutable $start, ?int $skillId): Builder
    {
        return PracticeAttempt::query()->where('student_id', $student->getAuthIdentifier())
            ->where('status', PracticeAttempt::STATUS_COMPLETED)->whereNotNull('practice_skill_id')
            ->when($start, fn (Builder $query) => $query->where('completed_at', '>=', $start))
            ->when($skillId, fn (Builder $query) => $query->where('practice_skill_id', $skillId));
    }

    private function skillAnalytics(User $student, ?CarbonImmutable $start, ?int $skillId): Collection
    {
        $periodRows = $this->attemptQuery($student, $start, $skillId)
            ->selectRaw('practice_skill_id, COUNT(*) attempts, SUM(total_questions) questions, SUM(correct_answers) correct_count, AVG(score) average_score')
            ->groupBy('practice_skill_id')->get()->keyBy('practice_skill_id');

        return StudentSkillProgress::query()->with(['skill.subject:id,name'])
            ->where('student_id', $student->getAuthIdentifier())
            ->when($skillId, fn (Builder $query) => $query->where('practice_skill_id', $skillId))
            ->get()->map(function (StudentSkillProgress $progress) use ($periodRows): array {
                $row = $periodRows->get($progress->practice_skill_id);
                $questions = (int) ($row?->questions ?? 0);

                return [
                    'id' => $progress->practice_skill_id,
                    'name' => $progress->skill->name,
                    'subject' => $progress->skill->subject->name,
                    'mastery_score' => $progress->mastery_score,
                    'mastery_level' => $progress->mastery_level,
                    'confidence_score' => $progress->confidence_score,
                    'attempts' => (int) ($row?->attempts ?? 0),
                    'questions' => $questions,
                    'accuracy' => $questions > 0 ? round(((int) $row->correct_count / $questions) * 100, 2) : 0,
                    'average_score' => round((float) ($row?->average_score ?? 0), 2),
                ];
            })->filter(fn (array $row): bool => $row['attempts'] > 0)->values();
    }

    private function improvement(Collection $trend): array
    {
        if ($trend->count() < 2) {
            $score = round((float) ($trend->last()?->average_score ?? 0), 2);

            return ['direction' => 'stable', 'change' => 0, 'current' => $score, 'previous' => $score];
        }
        $half = (int) ceil($trend->count() / 2);
        $previous = round((float) $trend->take($half)->avg('average_score'), 2);
        $current = round((float) $trend->skip($half)->avg('average_score'), 2);
        $change = round($current - $previous, 2);
        $threshold = (float) config('practice.analytics.trend_threshold');

        return [
            'direction' => $change >= $threshold ? 'improving' : ($change <= -$threshold ? 'declining' : 'stable'),
            'change' => $change,
            'current' => $current,
            'previous' => $previous,
        ];
    }

    private function period(string $period): array
    {
        $days = $period === 'all' ? null : (int) $period;

        return ['key' => $period, 'start' => $days ? CarbonImmutable::now()->startOfDay()->subDays($days - 1) : null, 'end' => CarbonImmutable::now()->endOfDay()];
    }
}
