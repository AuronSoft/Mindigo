<?php

namespace Mindigo\StudentPractice\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAnswer;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeSet;
use Mindigo\StudentPractice\Models\StudentSkillProgress;

class PracticeSetService
{
    public function listFor(User $user): LengthAwarePaginator
    {
        return PracticeSet::query()
            ->with(['creator', 'classroom'])
            ->withCount('questions')
            ->when(
                $user->isStudent(),
                fn (Builder $query) => $query->where(function (Builder $scope) use ($user): void {
                    $scope->where('creator_id', $user->getAuthIdentifier())
                        ->orWhereHas(
                            'classroom.students',
                            fn (Builder $students) => $students->whereKey($user->getAuthIdentifier())
                        );
                }),
                fn (Builder $query) => $user->isAdmin()
                    ? $query
                    : $query->where('creator_id', $user->getAuthIdentifier())
            )
            ->latest()
            ->paginate(12);
    }

    public function formData(User $user): array
    {
        return [
            'subjects' => Question::query()
                ->where('status', 'approved')
                ->whereNotNull('subject')
                ->distinct()
                ->orderBy('subject')
                ->pluck('subject'),
            'skills' => StudentSkillProgress::query()
                ->with('skill.subject')
                ->where('student_id', $user->getAuthIdentifier())
                ->orderBy('mastery_score')
                ->get()
                ->pluck('skill')
                ->filter(),
        ];
    }

    public function create(User $creator, array $data): PracticeSet
    {
        $questions = $this->questionQuery($creator, $data)
            ->inRandomOrder()
            ->limit((int) $data['question_count'])
            ->get();

        if ($questions->isEmpty()) {
            throw ValidationException::withMessages([
                'question_count' => __('student-practice::app.errors.no_questions'),
            ]);
        }

        return DB::transaction(function () use ($creator, $data, $questions): PracticeSet {
            $set = PracticeSet::query()->create([
                ...collect($data)->except(['question_count', 'skill_id'])->all(),
                'creator_id' => $creator->getAuthIdentifier(),
                'classroom_id' => null,
                'status' => PracticeSet::STATUS_READY,
            ]);
            $set->questions()->attach(
                $questions->values()->mapWithKeys(
                    fn (Question $question, int $index): array => [
                        $question->getKey() => ['position' => $index + 1],
                    ]
                )->all()
            );

            return $set->load(['creator', 'classroom', 'questions']);
        });
    }

    public function details(PracticeSet $set): PracticeSet
    {
        return $set->load(['creator', 'classroom', 'questions']);
    }

    public function findShared(string $token): PracticeSet
    {
        return PracticeSet::query()
            ->with(['creator:id,name', 'questions'])
            ->where('share_token', $token)
            ->where('is_shared', true)
            ->where('status', PracticeSet::STATUS_READY)
            ->firstOrFail();
    }

    public function share(PracticeSet $set, bool $enabled): PracticeSet
    {
        $set->update([
            'is_shared' => $enabled,
            'share_token' => $enabled ? ($set->share_token ?: (string) Str::uuid()) : $set->share_token,
        ]);

        return $set->fresh();
    }

    public function delete(PracticeSet $set): void
    {
        DB::transaction(fn () => $set->delete());
    }

    private function questionQuery(User $user, array $data): Builder
    {
        $query = Question::query()->where('status', 'approved');
        foreach (['subject', 'topic', 'difficulty'] as $field) {
            if (filled($data[$field] ?? null)) {
                $query->where($field, $data[$field]);
            }
        }

        if (($data['source'] ?? 'manual') === 'mistakes') {
            $practiceIds = PracticeAnswer::query()
                ->where('is_correct', false)
                ->whereHas(
                    'attempt',
                    fn (Builder $attempts) => $attempts
                        ->where('student_id', $user->getAuthIdentifier())
                        ->where('status', PracticeAttempt::STATUS_COMPLETED)
                )
                ->pluck('question_id');
            $examIds = ExamAttemptAnswer::query()
                ->where('is_correct', false)
                ->whereHas(
                    'attempt',
                    fn (Builder $attempts) => $attempts
                        ->where('user_id', $user->getAuthIdentifier())
                        ->whereIn('status', ['submitted', 'expired'])
                )
                ->whereHas('question', fn (Builder $questions) => $questions->whereNotNull('question_id'))
                ->with('question:id,question_id')
                ->get()
                ->pluck('question.question_id');
            $sessionExamIds = ExamSessionAttemptAnswer::query()
                ->where('is_correct', false)
                ->whereHas('attempt', fn (Builder $attempts) => $attempts->where('user_id', $user->getAuthIdentifier())->whereIn('status', ['submitted', 'expired', 'terminated']))
                ->whereHas('question', fn (Builder $questions) => $questions->whereNotNull('source_question_id'))
                ->with('question:id,source_question_id')
                ->get()
                ->pluck('question.source_question_id');

            $query->whereIn('id', $practiceIds->concat($examIds)->concat($sessionExamIds)->filter()->unique());
        }

        if (($data['source'] ?? 'manual') === 'weak_topics') {
            $skillId = $data['skill_id'] ?? StudentSkillProgress::query()
                ->where('student_id', $user->getAuthIdentifier())
                ->orderBy('mastery_score')
                ->value('practice_skill_id');

            $query->whereIn('id', DB::table('question_practice_skill')
                ->select('question_id')
                ->where('practice_skill_id', $skillId ?: 0));
        }

        return $query;
    }
}
