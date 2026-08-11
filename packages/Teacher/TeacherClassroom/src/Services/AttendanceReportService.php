<?php

namespace Mindigo\TeacherClassroom\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomAttendance;

class AttendanceReportService
{
    public function summary(Classroom $classroom): array
    {
        $base = $this->query($classroom);
        $summary = $this->totals($base);

        return [
            ...$summary,
            'sessions' => (clone $base)->distinct()->count('session_date'),
            'students' => (clone $base)->distinct()->count('student_id'),
            'course' => $classroom->course?->name,
            'course_summary' => $classroom->course_id ? $this->totals(
                ClassroomAttendance::query()->whereHas('classroom', fn (Builder $query) => $query->where('course_id', $classroom->course_id))
            ) : null,
        ];
    }

    /** @return Collection<int, object> */
    public function bySession(Classroom $classroom): Collection
    {
        return $this->query($classroom)
            ->selectRaw("session_date, COUNT(*) as total, SUM(status = 'present') as present, SUM(status = 'absent') as absent, SUM(status = 'late') as late, SUM(status = 'excused') as excused")
            ->groupBy('session_date')->orderByDesc('session_date')->limit(20)->get();
    }

    public function exportRows(Classroom $classroom): Collection
    {
        return $this->query($classroom)
            ->with(['student:id,name,email', 'editor:id,name', 'schedule:id,title'])
            ->orderByDesc('session_date')->orderBy('student_id')->get();
    }

    private function query(Classroom $classroom): Builder
    {
        return ClassroomAttendance::query()->where('classroom_id', $classroom->id);
    }

    private function totals(Builder $query): array
    {
        $totals = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'total' => (int) $totals->sum(),
            'present' => (int) ($totals['present'] ?? 0),
            'absent' => (int) ($totals['absent'] ?? 0),
            'late' => (int) ($totals['late'] ?? 0),
            'excused' => (int) ($totals['excused'] ?? 0),
        ];
    }
}
