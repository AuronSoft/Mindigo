<?php

namespace Mindigo\TeacherQuestion\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\QuestionBank\Models\QuestionFolder;
use Mindigo\QuestionBank\Services\QuestionBankService;
use Mindigo\TeacherQuestion\Http\Requests\TeacherQuestionRequest;

class TeacherQuestionService
{
    public function __construct(private readonly QuestionBankService $bank) {}

    public function filteredList(User $teacher, array $filters)
    {
        // QuestionBankService::filteredList() auto-scope khi !isAdmin()
        return $this->bank->filteredList($teacher, $filters);
    }

    public function importFromRows(array $rows, User $teacher, string $status, ?int $folderId): int
    {
        return $this->bank->importFromRows($rows, $teacher, $status, $folderId);
    }

    public function stats(User $teacher): array
    {
        $tid = $teacher->getAuthIdentifier();
        $base = Question::where('created_by', $tid);

        return [
            'total' => (clone $base)->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'reviewing' => (clone $base)->where('status', 'reviewing')->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
        ];
    }

    public function create(TeacherQuestionRequest $request): Question
    {
        return $this->bank->createQuestion($request->user(), $request->questionData());
    }

    public function update(Question $question, TeacherQuestionRequest $request): Question
    {
        return $this->bank->updateQuestion($question, $request->questionData($question));
    }

    public function submitForReview(Question $question): void
    {
        if ($question->status !== 'draft') {
            return;
        }

        $question->update(['status' => 'reviewing']);
    }

    public function delete(Question $question): void
    {
        $this->bank->delete($question);
    }

    public function myFolders(User $teacher): Collection
    {
        return QuestionFolder::where('created_by', $teacher->getAuthIdentifier())
            ->withCount('questions')
            ->orderBy('name')
            ->get();
    }

    public function import(UploadedFile $file, User $teacher, string $status, ?int $folderId): int
    {
        return $this->bank->import($file, $teacher, $status, $folderId);
    }

    public function formData(User $teacher): array
    {
        $data = $this->bank->formData($teacher);

        // Hạn chế folders chỉ là của giáo viên này
        $data['folders'] = $this->myFolders($teacher);

        return $data;
    }

    public function bulkUpdateDifficulty(User $teacher, array $ids, string $difficulty): void
    {
        Question::query()
            ->whereIn('id', $ids)
            ->where('created_by', $teacher->getAuthIdentifier())
            ->update(['difficulty' => $difficulty]);
    }

    public function bulkDelete(User $teacher, array $ids): void
    {
        $questions = Question::query()
            ->whereIn('id', $ids)
            ->where('created_by', $teacher->getAuthIdentifier())
            ->get();

        foreach ($questions as $question) {
            $this->bank->delete($question);
        }
    }

    public function bulkUpdateStatus(User $teacher, array $ids, string $status): void
    {
        Question::query()
            ->whereIn('id', $ids)
            ->where('created_by', $teacher->getAuthIdentifier())
            ->update([
                'status' => $status,
                'reviewed_by' => $status === 'approved' ? $teacher->getAuthIdentifier() : null,
                'reviewed_at' => $status === 'approved' ? now() : null,
            ]);
    }
}
