<?php

namespace Mindigo\StudentDashboard\Services;

use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherAssignment\Models\AssignmentSubmission;

class DashboardService
{
    /**
     * ID các lớp mà học sinh đang tham gia.
     */
    public function classroomIds(User $student): Collection
    {
        return Classroom::query()
            ->whereHas('students', fn ($q) => $q->where('student_id', $student->id))
            ->pluck('id');
    }

    /**
     * Các lớp của học sinh (kèm giáo viên).
     */
    public function getMyClassrooms(User $student): Collection
    {
        return Classroom::query()
            ->whereHas('students', fn ($q) => $q->where('student_id', $student->id))
            ->with('teacher')
            ->latest()
            ->take(6)
            ->get();
    }

    /**
     * Bài tập sắp đến hạn mà học sinh CHƯA nộp.
     */
    public function getUpcomingAssignments(User $student, Collection $classroomIds): Collection
    {
        if ($classroomIds->isEmpty()) {
            return collect();
        }

        $submittedIds = AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->pluck('assignment_id');

        return Assignment::query()
            ->whereIn('classroom_id', $classroomIds)
            ->where('status', 'published')
            ->whereNotIn('id', $submittedIds)
            ->where('due_date', '>=', now())
            ->with('classroom')
            ->orderBy('due_date')
            ->take(5)
            ->get();
    }

    /**
     * Đề thi đang mở (đã xuất bản, còn trong thời gian làm bài).
     */
    public function getOpenExams(): Collection
    {
        return Exam::query()
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderByRaw('starts_at IS NULL, starts_at ASC')
            ->take(5)
            ->get();
    }

    /**
     * Kết quả thi gần đây của học sinh.
     */
    public function getRecentResults(User $student): Collection
    {
        return ExamAttempt::query()
            ->where('user_id', $student->id)
            ->whereNotNull('submitted_at')
            ->with('exam')
            ->latest('submitted_at')
            ->take(5)
            ->get();
    }

    /**
     * Số liệu tổng quan cho các thẻ thống kê.
     */
    public function getStats(User $student, Collection $classroomIds): array
    {
        $submittedIds = AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->pluck('assignment_id');

        $pendingAssignments = $classroomIds->isEmpty() ? 0 : Assignment::query()
            ->whereIn('classroom_id', $classroomIds)
            ->where('status', 'published')
            ->whereNotIn('id', $submittedIds)
            ->where('due_date', '>=', now())
            ->count();

        $examsTaken = ExamAttempt::query()
            ->where('user_id', $student->id)
            ->whereNotNull('submitted_at')
            ->count();

        $avgScore = ExamAttempt::query()
            ->where('user_id', $student->id)
            ->whereNotNull('submitted_at')
            ->avg('percentage');

        return [
            'classrooms'          => $classroomIds->count(),
            'pending_assignments' => $pendingAssignments,
            'exams_taken'         => $examsTaken,
            'avg_score'           => $avgScore ? round((float) $avgScore, 1) : 0,
        ];
    }
}
