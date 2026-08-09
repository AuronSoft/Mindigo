<?php

namespace Mindigo\StudentProgress\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherAssignment\Models\AssignmentSubmission;
use Mindigo\TeacherClassroom\Models\Classroom;

class ProgressService
{
    // ID các lớp học sinh đang tham gia (status active)
    public function classroomIdsForStudent(int|string $studentId): Collection
    {
        return Classroom::query()
            ->whereHas('students', function ($q) use ($studentId) {
                $q->where('student_id', $studentId)
                    ->where('classroom_students.status', 'active');
            })
            ->pluck('id');
    }

    public function getClassroomsForStudent(int|string $studentId)
    {
        return Classroom::query()
            ->whereIn('id', $this->classroomIdsForStudent($studentId))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    /**
     * Tổng hợp tiến độ học sinh.
     * $classroomId: lọc theo 1 lớp (ảnh hưởng số liệu bài tập); đề thi luôn tính toàn hệ thống.
     */
    public function computeForStudent(User $student, ?int $classroomId = null): array
    {
        $classroomIds = $this->classroomIdsForStudent($student->id);
        $scopeIds = $classroomId
            ? collect([$classroomId])->intersect($classroomIds)->values()
            : $classroomIds;

        // Bài tập
        $assignments = Assignment::query()
            ->whereIn('classroom_id', $scopeIds->isEmpty() ? [0] : $scopeIds)
            ->where('status', 'published')
            ->get(['id', 'classroom_id', 'max_score']);

        $assignmentTotal = $assignments->count();

        $submissions = AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->with('assignment:id,max_score')
            ->get();

        $assignmentDone = $submissions->count();
        $gradedSubs = $submissions->whereIn('status', ['graded', 'returned'])
            ->filter(fn ($s) => ! is_null($s->score));

        // Đề thi (toàn hệ thống, audience = student)
        $examTotal = Exam::query()->where('status', 'published')->count();

        $attempts = ExamAttempt::query()
            ->where('user_id', $student->id)
            ->whereNotNull('submitted_at')
            ->get(['exam_id', 'percentage', 'submitted_at']);

        $examDone = $attempts->pluck('exam_id')->unique()->count();

        // Điểm trung bình (gộp % đề thi + % bài tập đã chấm)
        $scorePercents = collect()
            ->merge($attempts->pluck('percentage')->filter(fn ($v) => ! is_null($v)))
            ->merge($gradedSubs->map(fn ($s) => $s->scorePercent())->filter(fn ($v) => ! is_null($v)));

        $avgScore = $scorePercents->isNotEmpty() ? (int) round($scorePercents->avg()) : 0;

        return [
            'assignment' => [
                'done' => $assignmentDone,
                'total' => $assignmentTotal,
                'rate' => $this->rate($assignmentDone, $assignmentTotal),
            ],
            'exam' => [
                'done' => $examDone,
                'total' => $examTotal,
                'rate' => $this->rate($examDone, $examTotal),
            ],
            'graded_count' => $gradedSubs->count(),
            'avg_score' => $avgScore,
            'per_classroom' => $this->perClassroom($student, $classroomIds),
            'timeline' => $this->timeline($attempts, $gradedSubs),
        ];
    }

    /**
     * Tiến độ bài tập + điểm TB theo từng lớp.
     */
    protected function perClassroom(User $student, Collection $classroomIds): Collection
    {
        if ($classroomIds->isEmpty()) {
            return collect();
        }

        $classrooms = Classroom::query()
            ->whereIn('id', $classroomIds)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $assignments = Assignment::query()
            ->whereIn('classroom_id', $classroomIds)
            ->where('status', 'published')
            ->get(['id', 'classroom_id', 'max_score']);

        $submissions = AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        return $classrooms->map(function ($classroom) use ($assignments, $submissions) {
            $classAssignments = $assignments->where('classroom_id', $classroom->id);
            $total = $classAssignments->count();

            $done = 0;
            $percents = collect();

            foreach ($classAssignments as $a) {
                $sub = $submissions->get($a->id);
                if ($sub) {
                    $done++;
                    if (! is_null($sub->score) && $a->max_score > 0) {
                        $percents->push(round($sub->score / $a->max_score * 100, 1));
                    }
                }
            }

            return (object) [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'code' => $classroom->code,
                'done' => $done,
                'total' => $total,
                'rate' => $this->rate($done, $total),
                'avg_score' => $percents->isNotEmpty() ? (int) round($percents->avg()) : null,
            ];
        });
    }

    /**
     * Biểu đồ tiến bộ: điểm TB theo 6 tháng gần nhất.
     */
    protected function timeline(Collection $attempts, Collection $gradedSubs): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->startOfMonth()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('m/Y');

            $monthScores = collect()
                ->merge($attempts
                    ->filter(fn ($a) => $a->submitted_at && Carbon::parse($a->submitted_at)->format('Y-m') === $key)
                    ->pluck('percentage')
                    ->filter(fn ($v) => ! is_null($v)))
                ->merge($gradedSubs
                    ->filter(fn ($s) => $s->graded_at && Carbon::parse($s->graded_at)->format('Y-m') === $key)
                    ->map(fn ($s) => $s->scorePercent())
                    ->filter(fn ($v) => ! is_null($v)));

            $data[] = $monthScores->isNotEmpty() ? (int) round($monthScores->avg()) : 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    protected function rate(int $value, int $total): int
    {
        return $total > 0 ? (int) round($value / $total * 100) : 0;
    }
}
