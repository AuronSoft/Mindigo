<?php

namespace Mindigo\StudentHistory\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\TeacherAssignment\Models\AssignmentSubmission;

class HistoryService
{
    /**
     * Gộp lịch sử nộp bài + làm thi thành dòng thời gian.
     * $type: null | 'assignment' | 'exam'
     */
    public function timeline(int|string $studentId, ?string $type = null, int $perPage = 12): LengthAwarePaginator
    {
        $items = $this->collect($studentId, $type)->sortByDesc('at')->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $slice = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $items->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()],
        );
    }

    /**
     * Số liệu tổng quan: số bài đã nộp, số đề đã làm, điểm trung bình (%).
     */
    public function summary(int|string $studentId): array
    {
        $items = $this->collect($studentId, null);

        $percents = $items->pluck('percent')->filter(fn ($v) => ! is_null($v));

        return [
            'assignments' => $items->where('type', 'assignment')->count(),
            'exams' => $items->where('type', 'exam')->count(),
            'avg_score' => $percents->isNotEmpty() ? (int) round($percents->avg()) : 0,
        ];
    }

    // Gom 2 nguồn thành collection các (object) sự kiện

    protected function collect(int|string $studentId, ?string $type): Collection
    {
        $events = collect();

        if ($type === null || $type === 'assignment') {
            AssignmentSubmission::query()
                ->where('student_id', $studentId)
                ->whereNotNull('submitted_at')
                ->with(['assignment:id,title,max_score,classroom_id', 'assignment.classroom:id,name'])
                ->get()
                ->each(function ($s) use ($events) {
                    $percent = $s->scorePercent();
                    $events->push((object) [
                        'type' => 'assignment',
                        'title' => $s->assignment?->title ?? __('student-history::app.deleted_assignment'),
                        'classroom' => $s->assignment?->classroom?->name,
                        'score' => $s->score,
                        'max' => $s->assignment?->max_score,
                        'percent' => $percent,
                        'status' => $s->status,            // submitted | graded | returned
                        'graded' => in_array($s->status, ['graded', 'returned']),
                        'is_late' => (bool) $s->is_late,
                        'passed' => null,
                        'at' => $s->submitted_at,
                        'url' => Route::has('student.assignments.index') ? route('student.assignments.index') : null,
                    ]);
                });
        }

        if ($type === null || $type === 'exam') {
            ExamAttempt::query()
                ->whereNotIn('id', ExamSessionAttempt::query()->whereNotNull('legacy_exam_attempt_id')->select('legacy_exam_attempt_id'))
                ->where('user_id', $studentId)
                ->whereNotNull('submitted_at')
                ->with('exam:id,title,passing_score')
                ->get()
                ->each(function ($a) use ($events) {
                    $events->push((object) [
                        'type' => 'exam',
                        'title' => $a->exam?->title ?? __('student-history::app.deleted_exam'),
                        'classroom' => null,
                        'score' => $a->score,
                        'max' => $a->max_score,
                        'percent' => is_null($a->percentage) ? null : (int) round($a->percentage),
                        'status' => 'exam',
                        'graded' => true,
                        'is_late' => false,
                        'passed' => (bool) $a->passed,
                        'at' => $a->submitted_at,
                        'url' => Route::has('student.exams.index') ? route('student.exams.index') : null,
                    ]);
                });
            ExamSessionAttempt::query()
                ->where('user_id', $studentId)
                ->whereNotNull('submitted_at')
                ->with('session:id,title')
                ->get()
                ->each(function ($attempt) use ($events) {
                    $events->push((object) [
                        'type' => 'exam',
                        'title' => $attempt->session?->title ?? __('student-history::app.deleted_exam'),
                        'classroom' => null,
                        'score' => $attempt->score,
                        'max' => $attempt->max_score,
                        'percent' => is_null($attempt->percentage) ? null : (int) round((float) $attempt->percentage),
                        'status' => 'exam',
                        'graded' => ! $attempt->needs_review,
                        'is_late' => false,
                        'passed' => $attempt->passed,
                        'at' => $attempt->submitted_at,
                        'url' => Route::has('student.exam-sessions.index') ? route('student.exam-sessions.index') : null,
                    ]);
                });
        }

        return $events;
    }
}
