<?php

namespace Mindigo\StudentPractice\Services;

use Illuminate\Database\Eloquent\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAnswer;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeSkill;

class SkillQuestionSelector
{
    public function select(User $student, PracticeSkill $skill, int $count, ?string $difficulty = null): array
    {
        $query = $skill->questions()->practiceReady();
        if ($difficulty !== null) {
            $query->where('difficulty', $difficulty);
        }

        $poolSize = (clone $query)->count();
        $recentIds = PracticeAttempt::query()
            ->where('student_id', $student->getAuthIdentifier())
            ->where('practice_skill_id', $skill->getKey())
            ->latest('id')->limit((int) config('practice.adaptive.recent_attempts'))->pluck('id')
            ->pipe(fn ($ids) => $ids->isEmpty() ? collect() : PracticeAnswer::query()
                ->whereIn('attempt_id', $ids)->pluck('question_id'));

        $fresh = (clone $query)->whereNotIn('question_bank_questions.id', $recentIds)->get();
        $all = $fresh->count() >= $count ? $fresh : $fresh->concat(
            (clone $query)->whereNotIn('question_bank_questions.id', $fresh->pluck('id'))->get()
        );

        return [$this->balanced($all, $count), $poolSize];
    }

    public function selectAdaptive(User $student, PracticeSkill $skill, int $count, string $targetDifficulty): array
    {
        $questions = $skill->questions()->practiceReady()->get();
        $recentIds = $this->recentQuestionIds($student, $skill);
        $order = match ($targetDifficulty) {
            MasteryCalculator::DIFFICULTY_HARD => ['hard', 'medium', 'easy'],
            MasteryCalculator::DIFFICULTY_MEDIUM => ['medium', 'easy', 'hard'],
            default => ['easy', 'medium', 'hard'],
        };
        $selected = new Collection;
        foreach ([false, true] as $includeRecent) {
            foreach ($order as $difficulty) {
                $candidates = $questions->where('difficulty', $difficulty)
                    ->filter(fn (Question $question): bool => ! $selected->contains('id', $question->id))
                    ->filter(fn (Question $question): bool => $includeRecent || ! $recentIds->contains($question->id))
                    ->shuffle();
                $selected = $selected->concat($candidates->take($count - $selected->count()));
                if ($selected->count() >= $count) {
                    break 2;
                }
            }
        }

        return [$selected, $questions->count()];
    }

    private function recentQuestionIds(User $student, PracticeSkill $skill)
    {
        $attemptIds = PracticeAttempt::query()->where('student_id', $student->getAuthIdentifier())
            ->where('practice_skill_id', $skill->getKey())->latest('id')
            ->limit((int) config('practice.adaptive.recent_attempts'))->pluck('id');

        return $attemptIds->isEmpty() ? collect() : PracticeAnswer::query()
            ->whereIn('attempt_id', $attemptIds)->pluck('question_id');
    }

    private function balanced(Collection $questions, int $count): Collection
    {
        $groups = collect(Question::DIFFICULTIES)
            ->mapWithKeys(fn (string $difficulty): array => [$difficulty => $questions->where('difficulty', $difficulty)->shuffle()->values()]);
        $selected = new Collection;

        while ($selected->count() < $count && $groups->contains(fn ($group) => $group->isNotEmpty())) {
            foreach (Question::DIFFICULTIES as $difficulty) {
                if ($selected->count() >= $count) {
                    break;
                }
                if ($groups[$difficulty]->isNotEmpty()) {
                    $selected->push($groups[$difficulty]->shift());
                }
            }
        }

        return $selected;
    }
}
