<?php

namespace Mindigo\StudentPractice\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\QuestionBank\Services\QuestionPracticeReadinessService;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\SubjectManagement\Models\Subject;

class PracticeSkillService
{
    public function __construct(private readonly QuestionPracticeReadinessService $readiness) {}

    public function filteredList(array $filters): LengthAwarePaginator
    {
        return PracticeSkill::query()
            ->with(['subject:id,name', 'topic:id,name', 'creator:id,name'])
            ->withCount('questions')
            ->when(filled($filters['keyword'] ?? null), function (Builder $query) use ($filters): void {
                $keyword = trim((string) $filters['keyword']);
                $query->where(fn (Builder $scope) => $scope
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%"));
            })
            ->when(filled($filters['subject_id'] ?? null), fn (Builder $query) => $query->where('subject_id', $filters['subject_id']))
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('status', $filters['status']))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
    }

    public function formData(?PracticeSkill $skill = null): array
    {
        return [
            'skill' => $skill,
            'subjects' => Subject::query()
                ->with(['topics' => fn ($query) => $query->where('status', 'active')->orderBy('sort_order')->orderBy('name')])
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'parents' => PracticeSkill::query()
                ->where('status', PracticeSkill::STATUS_ACTIVE)
                ->when($skill, fn (Builder $query) => $query->whereKeyNot($skill->getKey()))
                ->orderBy('name')
                ->get(),
            'statuses' => PracticeSkill::STATUSES,
            'questions' => Question::query()
                ->where('status', 'approved')
                ->latest('updated_at')
                ->limit(200)
                ->get(['id', 'subject', 'topic', 'content', 'difficulty']),
        ];
    }

    public function activeCatalog(): Collection
    {
        return PracticeSkill::query()
            ->with(['subject:id,name', 'topic:id,name'])
            ->withCount(['questions' => fn (Builder $query) => $query->practiceReady()])
            ->where('status', PracticeSkill::STATUS_ACTIVE)
            ->whereHas('subject', fn (Builder $query) => $query->where('status', 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function create(User $user, array $data): PracticeSkill
    {
        return DB::transaction(function () use ($user, $data): PracticeSkill {
            $questionIds = $data['question_ids'] ?? [];
            unset($data['question_ids']);
            $skill = PracticeSkill::query()->create([
                ...$data,
                'created_by' => $user->getAuthIdentifier(),
                'updated_by' => $user->getAuthIdentifier(),
            ]);
            $this->syncQuestions($skill, $questionIds);

            return $skill;
        });
    }

    public function update(PracticeSkill $skill, User $user, array $data): PracticeSkill
    {
        return DB::transaction(function () use ($skill, $user, $data): PracticeSkill {
            $questionIds = $data['question_ids'] ?? [];
            unset($data['question_ids']);
            $skill->update([...$data, 'updated_by' => $user->getAuthIdentifier()]);
            $this->syncQuestions($skill, $questionIds);

            return $skill->fresh(['subject', 'topic']);
        });
    }

    public function delete(PracticeSkill $skill): void
    {
        DB::transaction(function () use ($skill): void {
            $questionIds = $skill->questions()->pluck('question_bank_questions.id');
            $skill->questions()->detach();
            $skill->children()->update(['parent_id' => null]);
            $skill->delete();
            Question::query()->whereKey($questionIds)->get()->each(fn (Question $question) => $this->readiness->refresh($question));
        });
    }

    private function syncQuestions(PracticeSkill $skill, array $questionIds): void
    {
        $previousIds = $skill->questions()->pluck('question_bank_questions.id');
        $allowedIds = Question::query()
            ->whereIn('id', $questionIds)
            ->where('status', 'approved')
            ->where('subject', $skill->subject()->value('name'))
            ->pluck('id');

        $skill->questions()->sync(
            $allowedIds->values()->mapWithKeys(
                fn (int $questionId, int $index): array => [$questionId => ['is_primary' => $index === 0, 'weight' => 100]]
            )->all()
        );

        Question::query()
            ->whereKey($previousIds->merge($allowedIds)->unique())
            ->get()
            ->each(fn (Question $question) => $this->readiness->refresh($question));
    }
}
