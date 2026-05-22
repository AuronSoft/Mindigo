<?php

namespace Mindigo\QuestionBank\Services;

use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\QuestionBank\Models\QuestionFolder;
use Mindigo\SubjectManagement\Models\Subject;

class QuestionBankService
{
    public function __construct(private QuestionImportService $imports)
    {
    }

    public function filteredList(User $user, array $filters)
    {
        $query = Question::query()
            ->with(['creator:id,name,email,role', 'reviewer:id,name,email,role', 'folder:id,name,color'])
            ->latest('updated_at');

        if (!$user->isAdmin()) {
            $query->where('created_by', $user->getAuthIdentifier());
        }

        if (filled($filters['folder_id'] ?? null)) {
            $filters['folder_id'] === 'none'
                ? $query->whereNull('folder_id')
                : $query->where('folder_id', (int) $filters['folder_id']);
        }

        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('content', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%")
                    ->orWhere('topic', 'like', "%{$keyword}%");
            });
        }

        foreach (['subject', 'type', 'difficulty', 'status'] as $filter) {
            if (filled($filters[$filter] ?? null)) {
                $query->where($filter, $filters[$filter]);
            }
        }

        return $query->paginate(12)->withQueryString();
    }

    public function createFolder(User $user, array $data): QuestionFolder
    {
        $folder = QuestionFolder::query()->create([
            ...$data,
            'created_by' => $user->getAuthIdentifier(),
        ]);

        $this->auditFolder('create', [], $folder->only(['id', 'name', 'subject', 'description', 'color']), $folder);

        return $folder;
    }

    public function import($file, User $user, string $status, ?int $defaultFolderId): int
    {
        $rows = $this->imports->rowsFromFile($file);

        if (empty($rows)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_empty'),
            ]);
        }

        $created = 0;
        foreach ($rows as $index => $row) {
            $question = Question::query()->create(
                $this->imports->questionDataFromRow($row, $user, $status, $defaultFolderId, $index + 2)
            );
            $this->auditQuestion('import', [], $question->only($this->auditFields()), $question);
            $created++;
        }

        return $created;
    }

    public function createQuestion(User $user, array $data): Question
    {
        $question = Question::query()->create([
            ...$data,
            'created_by' => $user->getAuthIdentifier(),
        ]);

        $this->auditQuestion('create', [], $question->only($this->auditFields()), $question);

        return $question;
    }

    public function updateQuestion(Question $question, array $data): Question
    {
        $oldValues = $question->only($this->auditFields());
        $question->fill($data)->save();

        $this->auditQuestion('update', $oldValues, $question->only($this->auditFields()), $question);

        return $question;
    }

    public function review(Question $question, User $reviewer, array $data): void
    {
        $oldValues = $question->only(['status', 'review_note', 'reviewed_by', 'reviewed_at']);
        $question->forceFill([
            'status' => $data['status'],
            'review_note' => $data['review_note'] ?? null,
            'reviewed_by' => $reviewer->getAuthIdentifier(),
            'reviewed_at' => now(),
        ])->save();

        $this->auditQuestion('review', $oldValues, $question->only(['status', 'review_note', 'reviewed_by', 'reviewed_at']), $question);
    }

    public function delete(Question $question): void
    {
        $oldValues = $question->only($this->auditFields());
        $question->delete();
        $this->auditQuestion('delete', $oldValues, [], $question);
    }

    public function formData(User $user): array
    {
        return [
            'types' => Question::TYPES,
            'difficulties' => Question::DIFFICULTIES,
            'subjects' => $this->subjects(),
            'subjectTopics' => $this->topicsBySubject(),
            'folders' => $this->foldersFor($user),
        ];
    }

    public function stats(User $user): array
    {
        $query = Question::query();

        if (!$user->isAdmin()) {
            $query->where('created_by', $user->getAuthIdentifier());
        }

        return [
            'total' => (clone $query)->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'reviewing' => (clone $query)->where('status', 'reviewing')->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
        ];
    }

    public function subjects(): array
    {
        if (class_exists(Subject::class)) {
            $subjects = Subject::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->filter()
                ->values()
                ->all();

            if (!empty($subjects)) {
                return $subjects;
            }
        }

        return Question::query()
            ->select('subject')
            ->whereNotNull('subject')
            ->distinct()
            ->orderBy('subject')
            ->pluck('subject')
            ->filter()
            ->values()
            ->all();
    }

    public function topicsBySubject(): array
    {
        if (class_exists(Subject::class)) {
            $subjects = Subject::query()
                ->with(['topics' => fn ($query) => $query
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->orderBy('name')])
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            if ($subjects->isNotEmpty()) {
                return $subjects
                    ->mapWithKeys(fn (Subject $subject) => [
                        $subject->name => $subject->topics->pluck('name')->values()->all(),
                    ])
                    ->all();
            }
        }

        return Question::query()
            ->select(['subject', 'topic'])
            ->whereNotNull('subject')
            ->whereNotNull('topic')
            ->orderBy('subject')
            ->orderBy('topic')
            ->get()
            ->groupBy('subject')
            ->map(fn ($rows) => $rows->pluck('topic')->filter()->unique()->values()->all())
            ->all();
    }

    public function foldersFor(User $user)
    {
        $query = QuestionFolder::query()
            ->withCount('questions')
            ->orderBy('name');

        if (!$user->isAdmin()) {
            $query->where('created_by', $user->getAuthIdentifier());
        }

        return $query->get();
    }

    public function can(User $user, string $permission): bool
    {
        return method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission);
    }

    public function canAccessQuestion(User $user, Question $question): bool
    {
        return $user->isAdmin() || (int) $question->created_by === (int) $user->getAuthIdentifier();
    }

    private function auditFields(): array
    {
        return ['id', 'folder_id', 'subject', 'topic', 'type', 'difficulty', 'status', 'content', 'options', 'correct_answers', 'tags'];
    }

    private function auditQuestion(string $action, array $oldValues, array $newValues, Question $question): void
    {
        if (!class_exists(\Mindigo\AuditLog\Services\AuditLogService::class)) {
            return;
        }

        app(\Mindigo\AuditLog\Services\AuditLogService::class)->record(
            $action,
            'questions',
            $oldValues,
            $newValues,
            ['question_id' => $question->id, 'subject' => $question->subject],
            $question
        );
    }

    private function auditFolder(string $action, array $oldValues, array $newValues, QuestionFolder $folder): void
    {
        if (!class_exists(\Mindigo\AuditLog\Services\AuditLogService::class)) {
            return;
        }

        app(\Mindigo\AuditLog\Services\AuditLogService::class)->record(
            $action,
            'question_folders',
            $oldValues,
            $newValues,
            ['folder_id' => $folder->id, 'name' => $folder->name],
            $folder
        );
    }
}
