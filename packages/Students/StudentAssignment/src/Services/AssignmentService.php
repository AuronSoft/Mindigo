<?php

namespace Mindigo\StudentAssignment\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherAssignment\Models\AssignmentSubmission;
use Mindigo\TeacherClassroom\Models\ClassroomStudent;

class AssignmentService
{
    public function getAssignmentsForStudent(int $studentId, array $filters = []): LengthAwarePaginator
    {
        return Assignment::query()
            ->published()
            ->whereHas('classroom.studentLinks', function ($query) use ($studentId) {
                $query->where('student_id', $studentId)->where('status', 'active');
            })
            ->with([
                'classroom:id,name,code',
                'submissions' => fn ($query) => $query->where('student_id', $studentId),
            ])
            ->when($filters['status'] ?? null, function ($query, string $status) use ($studentId) {
                if ($status === 'pending') {
                    $query->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $studentId));
                } elseif ($status === 'submitted') {
                    $query->whereHas('submissions', fn ($q) => $q->where('student_id', $studentId)->where('status', 'submitted'));
                } elseif ($status === 'graded') {
                    $query->whereHas('submissions', fn ($q) => $q->where('student_id', $studentId)->whereIn('status', ['graded', 'returned']));
                }
            })
            ->when($filters['classroom_id'] ?? null, fn ($query, $classroomId) => $query->where('classroom_id', $classroomId))
            ->orderByRaw('due_date < ? asc', [now()])
            ->orderByRaw(
                '(SELECT CASE
                    WHEN s.id IS NULL THEN 0
                    WHEN s.status IN (?, ?) THEN 2
                    ELSE 1
                END
                FROM assignment_submissions AS s
                WHERE s.assignment_id = assignments.id AND s.student_id = ?
                LIMIT 1) asc',
                ['graded', 'returned', $studentId]
            )
            ->orderBy('due_date')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();
    }

    public function assertStudentCanAccess(Assignment $assignment, int $studentId): void
    {
        abort_unless($assignment->status === 'published', 404);

        $isMember = ClassroomStudent::query()
            ->where('classroom_id', $assignment->classroom_id)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->exists();

        abort_unless($isMember, 403);
    }

    public function findSubmission(Assignment $assignment, int $studentId): ?AssignmentSubmission
    {
        return $assignment->submissions()->where('student_id', $studentId)->first();
    }

    public function submissionDeadline(Assignment $assignment)
    {
        if (! $assignment->allow_late) {
            return $assignment->due_date;
        }

        return $assignment->late_days
            ? $assignment->due_date->copy()->addDays($assignment->late_days)
            : null;
    }

    public function canSubmit(Assignment $assignment, ?AssignmentSubmission $submission = null): bool
    {
        if ($submission !== null) {
            return false;
        }

        $deadline = $this->submissionDeadline($assignment);

        return $deadline === null || now()->lte($deadline);
    }

    public function submit(Assignment $assignment, int $studentId, array $data, ?UploadedFile $file): AssignmentSubmission
    {
        return DB::transaction(function () use ($assignment, $studentId, $data, $file) {
            $submission = $this->findSubmission($assignment, $studentId);
            abort_unless($submission === null, 422, __('student-assignment::app.errors.already_submitted'));
            abort_unless($this->canSubmit($assignment, $submission), 422, __('student-assignment::app.errors.submission_closed'));

            $payload = [
                'assignment_id' => $assignment->id,
                'student_id' => $studentId,
                'submitted_at' => now(),
                'is_late' => $assignment->due_date ? now()->gt($assignment->due_date) : false,
                'status' => 'submitted',
                'score' => null,
                'feedback' => null,
                'graded_at' => null,
                'file_path' => null,
                'file_original_name' => null,
                'text_content' => null,
            ];

            if ($assignment->submission_type === 'both') {
                if (($data['submission_method'] ?? '') === 'file') {
                    abort_unless($file, 422, __('student-assignment::app.validation.file_required'));
                    $payload['file_path'] = $file->store('assignments/submissions', 'public');
                    $payload['file_original_name'] = $file->getClientOriginalName();
                } else {
                    $payload['text_content'] = $data['text_content'] ?? null;
                }
            } elseif ($assignment->allowsFile()) {
                abort_unless($file, 422, __('student-assignment::app.validation.file_required'));
                $payload['file_path'] = $file->store('assignments/submissions', 'public');
                $payload['file_original_name'] = $file->getClientOriginalName();
            } else {
                $payload['text_content'] = $data['text_content'] ?? null;
            }

            return AssignmentSubmission::query()->create($payload);
        });
    }
}
