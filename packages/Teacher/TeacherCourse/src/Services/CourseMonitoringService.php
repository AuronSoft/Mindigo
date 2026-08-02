<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Database\Eloquent\Builder;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;

class CourseMonitoringService
{
    public function report(Course $course, array $filters): array
    {
        $base = CourseEnrollment::query()
            ->where('course_id', $course->id)
            ->whereNotNull('distribution_id');

        $totalLessons = $course->lessons()->count();
        $summary = (clone $base)->selectRaw('COUNT(*) as assigned_count')
            ->selectRaw("SUM(CASE WHEN status IN ('in_progress', 'completed') THEN 1 ELSE 0 END) as started_count")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->selectRaw('COALESCE(AVG(completion_percentage), 0) as average_progress')
            ->first();

        $sortColumn = match ($filters['sort'] ?? null) {
            'student' => 'users.name',
            'progress' => 'course_enrollments.completion_percentage',
            default => 'course_enrollments.last_activity_at',
        };

        $enrollments = (clone $base)
            ->join('users', 'users.id', '=', 'course_enrollments.student_id')
            ->select('course_enrollments.*')
            ->with(['student:id,name,email,avatar', 'classroom:id,name', 'distribution'])
            ->withCount(['lessonProgress as completed_lessons_count' => fn (Builder $query) => $query->whereNotNull('completed_at')])
            ->when(filled($filters['search'] ?? null), fn (Builder $query) => $query->where(function (Builder $search) use ($filters): void {
                $value = '%'.trim($filters['search']).'%';
                $search->where('users.name', 'like', $value)->orWhere('users.email', 'like', $value);
            }))
            ->when(filled($filters['classroom_id'] ?? null), fn (Builder $query) => $query->where('course_enrollments.classroom_id', $filters['classroom_id']))
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('course_enrollments.status', $filters['status']))
            ->orderBy($sortColumn, $filters['direction'] ?? 'desc')
            ->paginate(20)
            ->withQueryString();

        return [
            'course' => $course,
            'summary' => $summary,
            'totalLessons' => $totalLessons,
            'enrollments' => $enrollments,
            'filters' => $filters,
            'classrooms' => $course->classroomAssignments()->with('classroom:id,name')->get()->pluck('classroom')->filter()->unique('id')->values(),
        ];
    }
}
