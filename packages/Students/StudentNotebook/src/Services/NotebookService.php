<?php

namespace Mindigo\StudentNotebook\Services;

use Illuminate\Support\Collection;
use Mindigo\StudentNotebook\Models\Note;

class NotebookService
{
    // Danh sách ghi chú của học sinh (mới cập nhật lên đầu)

    public function listForStudent(int|string $studentId): Collection
    {
        return Note::query()
            ->where('student_id', $studentId)
            ->latest('updated_at')
            ->get();
    }

    // Tìm 1 ghi chú thuộc về học sinh (null nếu không phải của mình)

    public function findForStudent(int $id, int|string $studentId): ?Note
    {
        return Note::query()
            ->where('id', $id)
            ->where('student_id', $studentId)
            ->first();
    }

    public function create(int|string $studentId, array $data): Note
    {
        return Note::create([
            'student_id' => $studentId,
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
        ]);
    }

    public function update(Note $note, array $data): Note
    {
        $note->update([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
        ]);

        return $note->fresh();
    }

    public function delete(Note $note): void
    {
        $note->delete();
    }
}
