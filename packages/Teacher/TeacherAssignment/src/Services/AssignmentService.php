<?php

namespace Mindigo\TeacherAssignment\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherAssignment\Models\AssignmentSubmission;

class AssignmentService
{
    //Lấy danh sách bài tập của GV

    public function getAssignmentsByTeacher(int|string $teacherId, int $perPage = 15)
    {
        return Assignment::query()
            ->byTeacher($teacherId)
            ->with('classroom')
            ->latest()
            ->paginate($perPage);
    }

    //Tạo bài tập

    public function create(array $data, ?array $files = null): Assignment
    {
        if ($files && count($files) > 0) {
            $paths = [];
            foreach ($files as $file) {
                $paths[] = $file->store('assignments/files', 'public');
            }
            $data['file_path'] = $paths;
        }
        $data['teacher_id'] = auth()->id();

        return Assignment::create($data);
    }

    // Cập nhật bài tập

    public function update(Assignment $assignment, array $data, ?array $files = null): Assignment
    {
        $removeFiles = $data['remove_files'] ?? [];
        unset($data['remove_files']);

        $currentPaths = is_array($assignment->file_path) ? $assignment->file_path : [];

        if (! empty($removeFiles)) {
            $removeFiles = array_values(array_intersect($removeFiles, $currentPaths));

            foreach ($removeFiles as $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $currentPaths = array_values(array_diff($currentPaths, $removeFiles));
        }

        if ($files && count($files) > 0) {
            foreach ($files as $file) {
                $currentPaths[] = $file->store('assignments/files', 'public');
            }
        }

        $data['file_path'] = count($currentPaths) > 0 ? $currentPaths : null;

        $assignment->update($data);
        return $assignment->fresh();
    }

    //Xoá bài tập

    public function delete(Assignment $assignment): void
    {
        if ($assignment->file_path) {
            foreach ($assignment->file_path as $path) {
                Storage::disk('public')->delete($path);
            }
        }
        $assignment->delete();
    }

    //Chấm bài 

    public function grade(AssignmentSubmission $submission, array $data): AssignmentSubmission
    {
        $submission->update([
            'score'     => $data['score'],
            'feedback'  => $data['feedback'] ?? null,
            'status'    => $data['status'],
            'graded_at' => now(),
        ]);

        return $submission->fresh();
    }

    // Lấy danh sách SV trong lớp kèm trạng thái bài nộp
    // Trả về tất cả SV — ai nộp thì có submission, ai chưa thì null

    public function getStudentSubmissionList(Assignment $assignment): \Illuminate\Support\Collection
    {
        $students   = $assignment->classroom->students; // many-to-many
        $submissions = $assignment->submissions->keyBy('student_id');

        return $students->map(function ($student) use ($submissions) {
            return (object) [
                'student'    => $student,
                'submission' => $submissions->get($student->id), // null = chưa nộp
            ];
        });
    }

    //Thống kê
    public function getStats(Assignment $assignment): array
    {
        $submissions  = $assignment->submissions;
        $totalStudents = $assignment->classroom->students()->count();

        return [
            'total_students' => $totalStudents,
            'submitted'      => $submissions->count(),
            'not_submitted'  => $totalStudents - $submissions->count(),
            'graded'         => $submissions->whereIn('status', ['graded', 'returned'])->count(),
            'late'           => $submissions->where('is_late', true)->count(),
            'avg_score'      => round($submissions->whereNotNull('score')->avg('score'), 2),
        ];
    }
}
