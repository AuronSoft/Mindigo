<?php

namespace Mindigo\TeacherCourse\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseReportService
{
    public function export(User $actor, string $scope, ?int $entityId, string $format): Response|StreamedResponse
    {
        [$headers, $rows] = $this->dataset($actor, $scope, $entityId);
        $filename = 'mindigo-course-'.$scope.'-report-'.now()->format('Ymd-His');

        if ($format === 'pdf') {
            return Pdf::loadView('teacher-course::analytics.report-pdf', compact('headers', 'rows', 'scope'))
                ->download($filename.'.pdf');
        }

        $delimiter = $format === 'xlsx' ? "\t" : ',';
        $extension = $format === 'xlsx' ? 'xls' : 'csv';
        $mime = $format === 'xlsx' ? 'application/vnd.ms-excel' : 'text/csv';

        return response()->streamDownload(function () use ($headers, $rows, $delimiter): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $headers, $delimiter);
            foreach ($rows as $row) {
                fputcsv($stream, array_values($row), $delimiter);
            }
            fclose($stream);
        }, $filename.'.'.$extension, ['Content-Type' => $mime]);
    }

    public function dataset(User $actor, string $scope, ?int $entityId): array
    {
        $query = CourseEnrollment::query()->with(['course:id,name,teacher_id', 'course.teacher:id,name', 'student:id,name,email', 'classroom:id,name']);
        if ($actor->isTeacher()) {
            $query->whereHas('course', fn ($course) => $course->where('teacher_id', $actor->id));
        }

        match ($scope) {
            'course' => $query->when($entityId, fn ($query) => $query->where('course_id', $entityId)),
            'teacher' => $query->whereHas('course', fn ($course) => $course->where('teacher_id', $entityId ?? $actor->id)),
            'student' => $query->when($entityId, fn ($query) => $query->where('student_id', $entityId)),
            'classroom' => $query->when($entityId, fn ($query) => $query->where('classroom_id', $entityId)),
        };

        $rows = $query->latest()->limit(10000)->get()->map(fn (CourseEnrollment $enrollment) => [
            $enrollment->course?->name, $enrollment->course?->teacher?->name, $enrollment->student?->name,
            $enrollment->student?->email, $enrollment->classroom?->name, $enrollment->status,
            $enrollment->completion_percentage.'%', round($enrollment->time_spent_seconds / 60, 1),
            $enrollment->last_activity_at?->format('Y-m-d H:i'),
        ]);

        return [[
            __('teacher-course::analytics.course'), __('teacher-course::analytics.teacher'), __('teacher-course::analytics.student'),
            __('teacher-course::analytics.email'), __('teacher-course::analytics.classroom'), __('teacher-course::analytics.status'),
            __('teacher-course::analytics.progress'), __('teacher-course::analytics.learning_minutes'), __('teacher-course::analytics.last_activity'),
        ], $rows];
    }
}
