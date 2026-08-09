<?php

namespace Mindigo\StudentPractice\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAnswer;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeSet;

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
            'classrooms' => $this->classroomsFor($user),
            'subjects' => Question::query()
                ->where('status', 'approved')
                ->whereNotNull('subject')
                ->distinct()
                ->orderBy('subject')
                ->pluck('subject'),
        ];
    }

    public function create(User $creator, array $data): PracticeSet
    {
        $this->ensureClassroomOwnership($creator, $data['classroom_id'] ?? null);
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
                ...collect($data)->except('question_count')->all(),
                'creator_id' => $creator->getAuthIdentifier(),
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

            $query->whereIn('id', $practiceIds->concat($examIds)->filter()->unique());
        }

        return $query;
    }

    private function classroomsFor(User $user): Collection
    {
        if (! $user->isTeacher()) {
            return new Collection;
        }

        return Classroom::query()
            ->where('teacher_id', $user->getAuthIdentifier())
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function ensureClassroomOwnership(User $user, mixed $classroomId): void
    {
        if (! $classroomId) {
            return;
        }

        $ownsClassroom = $user->isTeacher() && Classroom::query()
            ->whereKey($classroomId)
            ->where('teacher_id', $user->getAuthIdentifier())
            ->exists();

        abort_unless($ownsClassroom, 403);
    }
}
