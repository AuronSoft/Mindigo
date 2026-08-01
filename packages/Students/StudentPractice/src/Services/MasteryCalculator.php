<?php

namespace Mindigo\StudentPractice\Services;

use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\StudentPractice\Models\PracticeAnswer;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeSkill;

class MasteryCalculator
{
    public const VERSION = 'v1';

    public const DIFFICULTY_EASY = 'easy';

    public const DIFFICULTY_MEDIUM = 'medium';

    public const DIFFICULTY_HARD = 'hard';

    public function calculate(User $student, PracticeSkill $skill): array
    {
        $answers = PracticeAnswer::query()
            ->join('student_practice_attempts as attempts', 'attempts.id', '=', 'student_practice_answers.attempt_id')
            ->where('attempts.student_id', $student->getAuthIdentifier())
            ->where('attempts.practice_skill_id', $skill->getKey())
            ->where('attempts.status', PracticeAttempt::STATUS_COMPLETED)
            ->latest('student_practice_answers.answered_at')
            ->limit((int) config('practice.adaptive.answer_window'))
            ->get(['student_practice_answers.is_correct', 'student_practice_answers.difficulty_snapshot']);

        if ($answers->isEmpty()) {
            return $this->emptyResult();
        }

        $weightedTotal = 0.0;
        $weightedCorrect = 0.0;
        foreach ($answers as $index => $answer) {
            $weight = $this->difficultyWeight($answer->difficulty_snapshot) * max(0.5, 1 - ($index * 0.02));
            $weightedTotal += $weight;
            $weightedCorrect += $answer->is_correct ? $weight : 0;
        }

        $score = round(($weightedCorrect / $weightedTotal) * 100, 2);
        [$correctStreak, $incorrectStreak] = $this->streaks($answers);
        $confidence = min(100, $answers->count() * (int) config('practice.adaptive.confidence_per_answer'));

        return [
            'mastery_score' => $score,
            'mastery_level' => $this->level($score),
            'confidence_score' => $confidence,
            'recommended_difficulty' => $this->difficulty($score, $confidence),
            'consecutive_correct' => $correctStreak,
            'consecutive_incorrect' => $incorrectStreak,
            'engine_version' => self::VERSION,
            'last_evaluated_at' => now(),
        ];
    }

    private function emptyResult(): array
    {
        return [
            'mastery_score' => 0,
            'mastery_level' => 'novice',
            'confidence_score' => 0,
            'recommended_difficulty' => self::DIFFICULTY_EASY,
            'consecutive_correct' => 0,
            'consecutive_incorrect' => 0,
            'engine_version' => self::VERSION,
            'last_evaluated_at' => now(),
        ];
    }

    private function difficultyWeight(?string $difficulty): float
    {
        return match ($difficulty) {
            self::DIFFICULTY_HARD => 1.2,
            self::DIFFICULTY_MEDIUM => 1.0,
            default => 0.8,
        };
    }

    private function level(float $score): string
    {
        return match (true) {
            $score >= (float) config('practice.adaptive.mastered_mastery') => 'mastered',
            $score >= (float) config('practice.adaptive.proficient_mastery') => 'proficient',
            $score >= (float) config('practice.adaptive.developing_mastery') => 'developing',
            default => 'novice',
        };
    }

    private function difficulty(float $score, float $confidence): string
    {
        if ($confidence < (float) config('practice.adaptive.minimum_difficulty_confidence')
            || $score < (float) config('practice.adaptive.medium_difficulty_mastery')) {
            return self::DIFFICULTY_EASY;
        }

        return $score >= (float) config('practice.adaptive.hard_difficulty_mastery')
            ? self::DIFFICULTY_HARD
            : self::DIFFICULTY_MEDIUM;
    }

    private function streaks(Collection $answers): array
    {
        $first = $answers->first()->is_correct;
        $length = $answers->takeWhile(fn ($answer): bool => $answer->is_correct === $first)->count();

        return $first ? [$length, 0] : [0, $length];
    }
}
