<?php

namespace Mindigo\StudentClassroom\Services;

use Illuminate\Support\Collection;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherLiveSession\Models\LiveSession;

class ClassroomService
{
    // ID các lớp mà học sinh đang tham gia (status active)

    public function classroomIdsForStudent(int|string $studentId): Collection
    {
        return Classroom::query()
            ->whereHas('students', function ($q) use ($studentId) {
                $q->where('student_id', $studentId)
                    ->where('classroom_students.status', 'active');
            })
            ->pluck('id');
    }

    // Danh sách lớp của học sinh (kèm GV, môn học, sĩ số)

    public function getClassroomsForStudent(int|string $studentId)
    {
        return Classroom::query()
            ->whereIn('id', $this->classroomIdsForStudent($studentId))
            ->with(['teacher', 'subjects'])
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }

    // Học sinh có thuộc lớp này không (chặn truy cập chéo)

    public function isEnrolled(Classroom $classroom, int|string $studentId): bool
    {
        return $classroom->students()
            ->where('student_id', $studentId)
            ->wherePivot('status', 'active')
            ->exists();
    }

    // Chi tiết lớp cho học sinh: GV, môn, lịch sắp tới, thông báo, bài tập, buổi học trực tuyến

    public function getClassroomDetail(Classroom $classroom): array
    {
        $classroom->load(['teacher', 'assistant', 'subjects']);

        // Lịch học sắp tới
        $schedules = $classroom->schedules()
            ->whereDate('session_date', '>=', now()->toDateString())
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->limit(8)
            ->get();

        // Thông báo đã phát hành tới lớp (published_at != null)
        $announcements = $classroom->announcements()
            ->whereNotNull('teacher_announcements.published_at')
            ->orderByDesc('teacher_announcements.published_at')
            ->limit(10)
            ->get();

        // Bài tập đã phát hành của lớp
        $assignments = Assignment::query()
            ->where('classroom_id', $classroom->id)
            ->where('status', 'published')
            ->latest('due_date')
            ->get();

        // Buổi học trực tuyến của lớp (trừ huỷ)
        $liveSessions = LiveSession::query()
            ->where('classroom_id', $classroom->id)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('scheduled_start')
            ->limit(10)
            ->get();

        // Thành viên trong lớp
        $members = $classroom->students()
            ->wherePivot('status', 'active')
            ->orderBy('name')
            ->get();

        return compact('schedules', 'announcements', 'assignments', 'liveSessions', 'members');
    }
}
