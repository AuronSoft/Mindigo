<?php

namespace Mindigo\TeacherResult\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;

class TeacherResultService
{
    public function overview(User $teacher, ?Classroom $classroom = null): array
    {
        $tid = (int) $teacher->getAuthIdentifier();
        $studentIds = $this->studentIdsFor($teacher, $classroom);

        $submittedAttempts = $this->submittedAttemptsFor($teacher, $studentIds);

        $totalAttempts = (clone $submittedAttempts)->count();
        $passedAttempts = (clone $submittedAttempts)->where('passed', true)->count();
        $avgScore = ((clone $submittedAttempts)->avg('percentage') ?? 0) / 10;

        $totalExamsQuery = Exam::query()->where('created_by', $tid);
        if ($classroom) {
            $totalExamsQuery->whereHas('attempts', fn (Builder $q) => $q->whereIn('user_id', $studentIds));
        }

        $trend = (clone $submittedAttempts)
            ->where('submitted_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(submitted_at) as date, COUNT(*) as count, ROUND(AVG(percentage) / 10, 1) as avg_score')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $trendData = collect(range(13, 0))->map(function ($i) use ($trend) {
            $day = now()->subDays($i);
            $date = $day->toDateString();

            return [
                'label' => $day->locale('vi')->isoFormat('D/M'),
                'count' => $trend[$date]->count ?? 0,
                'avg_score' => $trend[$date]->avg_score ?? null,
            ];
        });

        return [
            'total_attempts' => $totalAttempts,
            'passed_attempts' => $passedAttempts,
            'pass_rate' => $totalAttempts > 0 ? round($passedAttempts / $totalAttempts * 100, 1) : 0,
            'avg_score' => round($avgScore, 1),
            'total_exams' => (clone $totalExamsQuery)->count(),
            'total_students' => $studentIds->count(),
            'trend' => $trendData,
        ];
    }

    public function examResults(User $teacher, string $keyword = '', ?Classroom $classroom = null): Collection
    {
        $studentIds = $this->studentIdsFor($teacher, $classroom);

        if ($classroom && $studentIds->isEmpty()) {
            return collect();
        }

        $attemptFilter = function (Builder $q) use ($studentIds, $classroom): void {
            $q->where('status', 'submitted');
            if ($classroom) {
                $q->whereIn('user_id', $studentIds);
            }
        };

        $query = Exam::query()
            ->where('created_by', $teacher->getAuthIdentifier())
            ->withCount(['attempts' => $attemptFilter])
            ->withAvg(['attempts' => $attemptFilter], 'percentage')
            ->orderByDesc('updated_at');

        if ($classroom) {
            $query->whereHas('attempts', fn (Builder $q) => $q->whereIn('user_id', $studentIds));
        }

        if ($keyword) {
            $query->where(fn (Builder $q) => $q->where('title', 'like', "%{$keyword}%")->orWhere('subject', 'like', "%{$keyword}%"));
        }

        return $query->limit(20)->get()->map(function (Exam $exam) use ($studentIds, $classroom) {
            $passedQuery = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('status', 'submitted')
                ->where('passed', true);

            if ($classroom) {
                $passedQuery->whereIn('user_id', $studentIds);
            }

            $passed = $passedQuery->count();

            return [
                'exam' => $exam,
                'attempts' => $exam->attempts_count,
                'avg_score' => round(($exam->attempts_avg_percentage ?? 0) / 10, 1),
                'pass_rate' => $exam->attempts_count > 0 ? round($passed / $exam->attempts_count * 100, 1) : 0,
                'passed' => $passed,
            ];
        });
    }

    public function studentResults(User $teacher, string $keyword = '', ?Classroom $classroom = null): Collection
    {
        $studentIds = $this->studentIdsFor($teacher, $classroom);

        $query = User::students()
            ->whereIn('id', $studentIds)
            ->select('id', 'name', 'email');

        if ($keyword) {
            $query->where(fn (Builder $q) => $q->where('name', 'like', "%{$keyword}%")->orWhere('email', 'like', "%{$keyword}%"));
        }

        return $query->limit(50)->get()->map(function (User $student) use ($teacher) {
            $attempts = ExamAttempt::query()
                ->where('user_id', $student->id)
                ->whereHas('exam', fn (Builder $q) => $q->where('created_by', $teacher->getAuthIdentifier()))
                ->where('status', 'submitted');

            $total = (clone $attempts)->count();
            $passed = (clone $attempts)->where('passed', true)->count();
            $avg = ((clone $attempts)->avg('percentage') ?? 0) / 10;
            $last = (clone $attempts)->latest('submitted_at')->value('submitted_at');

            return [
                'student' => $student,
                'total' => $total,
                'passed' => $passed,
                'pass_rate' => $total > 0 ? round($passed / $total * 100) : 0,
                'avg_score' => round($avg, 1),
                'last_at' => $last,
            ];
        })->sortByDesc('avg_score')->values();
    }

    public function studentDetail(User $teacher, User $student, ?Classroom $classroom = null): array
    {
        if ($classroom) {
            abort_unless(
                $classroom->students()->whereKey($student->id)->exists(),
                404
            );
        }

        $history = ExamAttempt::query()
            ->where('user_id', $student->id)
            ->whereHas('exam', fn (Builder $q) => $q->where('created_by', $teacher->getAuthIdentifier()))
            ->where('status', 'submitted')
            ->with('exam:id,title,subject')
            ->orderByDesc('submitted_at')
            ->get();

        $bySubject = $history->whereNotNull('exam.subject')
            ->groupBy(fn ($attempt) => $attempt->exam->subject)
            ->map(fn ($group) => [
                'subject' => $group->first()->exam->subject,
                'count' => $group->count(),
                'avg_score' => round($group->avg('percentage') / 10, 1),
                'pass_rate' => round($group->where('passed', true)->count() / max(1, $group->count()) * 100),
            ])->values();

        return [
            'history' => $history,
            'by_subject' => $bySubject,
            'total' => $history->count(),
            'avg_score' => round(($history->avg('percentage') ?? 0) / 10, 1),
            'pass_rate' => $history->count() > 0 ? round($history->where('passed', true)->count() / $history->count() * 100, 1) : 0,
        ];
    }

    public function examDetail(User $teacher, Exam $exam, ?Classroom $classroom = null): array
    {
        abort_unless($exam->created_by === (int) $teacher->getAuthIdentifier() || $teacher->isAdmin(), 403);

        $attempts = ExamAttempt::where('exam_id', $exam->id)->where('status', 'submitted');

        if ($classroom) {
            $attempts->whereIn('user_id', $this->studentIdsFor($teacher, $classroom));
        }

        $total = (clone $attempts)->count();
        $passed = (clone $attempts)->where('passed', true)->count();
        $avgScore = ((clone $attempts)->avg('percentage') ?? 0) / 10;

        $distribution = [];
        foreach (['0-2' => [0, 20], '2-4' => [20, 40], '4-6' => [40, 60], '6-8' => [60, 80], '8-10' => [80, 101]] as $label => [$min, $max]) {
            $distribution[$label] = (clone $attempts)->where('percentage', '>=', $min)->where('percentage', '<', $max)->count();
        }

        $list = (clone $attempts)->with('user:id,name,email', 'answers')->orderByDesc('percentage')->limit(50)->get();

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $total - $passed,
            'pass_rate' => $total > 0 ? round($passed / $total * 100, 1) : 0,
            'avg_score' => round($avgScore, 1),
            'distribution' => $distribution,
            'list' => $list,
        ];
    }

    public function getMyClassrooms(User $teacher): Collection
    {
        return Classroom::where('teacher_id', $teacher->getAuthIdentifier())
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }

    private function studentIdsFor(User $teacher, ?Classroom $classroom = null): Collection
    {
        $query = DB::table('classroom_students')
            ->join('classrooms', 'classrooms.id', '=', 'classroom_students.classroom_id')
            ->where('classrooms.teacher_id', $teacher->getAuthIdentifier())
            ->whereNull('classrooms.deleted_at');

        if ($classroom) {
            $query->where('classroom_students.classroom_id', $classroom->id);
        }

        return $query->pluck('classroom_students.student_id')->unique()->values();
    }

    private function submittedAttemptsFor(User $teacher, Collection $studentIds): Builder
    {
        return ExamAttempt::query()
            ->whereHas('exam', fn (Builder $q) => $q->where('created_by', $teacher->getAuthIdentifier()))
            ->whereIn('user_id', $studentIds)
            ->where('status', 'submitted');
    }

    public function gradeManualAnswers(ExamAttempt $attempt, array $grades): void
    {
        DB::transaction(function () use ($attempt, $grades) {
            foreach ($grades as $answerId => $points) {
                $answer = ExamAttemptAnswer::find($answerId);
                if (! $answer || $answer->exam_attempt_id !== $attempt->id) {
                    continue;
                }

                $maxPoints = (float) ($answer->question?->points ?? 0);
                $awarded = min((float) $points, $maxPoints); // không vượt điểm tối đa

                $answer->update([
                    'points_awarded' => $awarded,
                    'is_correct' => $awarded >= $maxPoints,
                    'needs_review' => false,
                ]);
            }

            // Tính lại tổng điểm
            $attempt->load('answers');
            $score = (float) $attempt->answers->sum('points_awarded');
            $maxScore = (float) $attempt->max_score;
            $percentage = $maxScore > 0 ? round($score / $maxScore * 100, 2) : 0;

            $stillPending = $attempt->answers->where('needs_review', true)->count();

            $attempt->forceFill([
                'score' => $score,
                'percentage' => $percentage,
                'passed' => $stillPending > 0
                    ? null
                    : ($score >= (float) $attempt->exam->passing_score),
            ])->save();
        });
    }
}
