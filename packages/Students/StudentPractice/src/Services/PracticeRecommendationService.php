<?php

namespace Mindigo\StudentPractice\Services;

use Illuminate\Database\Eloquent\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\StudentPractice\Models\PracticeRecommendation;
use Mindigo\StudentPractice\Models\StudentSkillProgress;

class PracticeRecommendationService
{
    public function refresh(StudentSkillProgress $progress): PracticeRecommendation
    {
        [$type, $priority, $reason] = match (true) {
            $progress->consecutive_incorrect >= (int) config('practice.adaptive.incorrect_streak_review') => [PracticeRecommendation::TYPE_REVIEW, 100, 'incorrect_streak'],
            $progress->mastery_score < (float) config('practice.adaptive.medium_difficulty_mastery') => [PracticeRecommendation::TYPE_REVIEW, 90, 'low_mastery'],
            $progress->mastery_score >= (float) config('practice.adaptive.mastered_mastery') && $progress->confidence_score >= 50 => [PracticeRecommendation::TYPE_ADVANCE, 50, 'skill_mastered'],
            default => [PracticeRecommendation::TYPE_CONTINUE, 70, 'build_mastery'],
        };

        return PracticeRecommendation::query()->updateOrCreate(
            ['student_id' => $progress->student_id, 'practice_skill_id' => $progress->practice_skill_id],
            [
                'type' => $type,
                'priority' => $priority,
                'target_difficulty' => $progress->recommended_difficulty,
                'reason_code' => $reason,
                'reason_context' => [
                    'mastery_score' => $progress->mastery_score,
                    'confidence_score' => $progress->confidence_score,
                    'consecutive_correct' => $progress->consecutive_correct,
                    'consecutive_incorrect' => $progress->consecutive_incorrect,
                ],
                'engine_version' => MasteryCalculator::VERSION,
                'status' => PracticeRecommendation::STATUS_ACTIVE,
                'generated_at' => now(),
                'expires_at' => now()->addDays((int) config('practice.adaptive.recommendation_expiry_days')),
            ]
        );
    }

    public function feed(User $student): Collection
    {
        return PracticeRecommendation::query()->with(['skill.subject:id,name'])
            ->where('student_id', $student->getAuthIdentifier())
            ->where('status', PracticeRecommendation::STATUS_ACTIVE)
            ->whereHas('skill', fn ($query) => $query->where('status', 'active'))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByDesc('priority')->orderByDesc('generated_at')->get();
    }
}
