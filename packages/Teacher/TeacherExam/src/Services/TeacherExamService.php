<?php

namespace Mindigo\TeacherExam\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\ExamManagement\Services\ExamService;
use Mindigo\TeacherExam\Http\Requests\TeacherExamRequest;

class TeacherExamService
{
    public function __construct(private readonly ExamService $exams) {}

    /**
     * Danh sách đề thi CHỈ của giáo viên đang đăng nhập.
     */
    public function ownedList(User $teacher, array $filters = []): LengthAwarePaginator
    {
        $query = Exam::query()
            ->where('created_by', $teacher->getAuthIdentifier())
            ->withCount(['attempts' => fn ($q) => $q->where('status', 'submitted')])
            ->withAvg(['attempts' => fn ($q) => $q->where('status', 'submitted')], 'percentage')
            ->latest('updated_at');

        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%");
            });
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(12)->withQueryString();
    }

    public function stats(User $teacher): array
    {
        $base = Exam::query()->where('created_by', $teacher->getAuthIdentifier());

        return [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->where('status', 'published')->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
            'closed' => (clone $base)->where('status', 'closed')->count(),
        ];
    }

    public function create(TeacherExamRequest $request): Exam
    {
        return $this->exams->create($request);
    }

    public function update(Exam $exam, TeacherExamRequest $request): Exam
    {
        return $this->exams->update($exam, $request);
    }

    public function publish(Exam $exam): void
    {
        $this->exams->publish($exam);
    }

    public function close(Exam $exam): void
    {
        $this->exams->close($exam);
    }

    public function delete(Exam $exam): void
    {
        $this->exams->delete($exam);
    }

    public function formData(User $teacher): array
    {
        $classrooms = Classroom::query()
            ->when(! $teacher->isAdmin(), fn ($query) => $query->where('teacher_id', $teacher->getAuthIdentifier()))
            ->where('status', 'active')
            ->withCount(['students' => fn ($query) => $query->where('classroom_students.status', 'active')])
            ->orderBy('name')
            ->get();

        return [...$this->exams->formData(), 'classrooms' => $classrooms];
    }

    /**
     * Kết quả chi tiết của một đề thi (cho teacher xem).
     */
    public function examResults(Exam $exam): array
    {
        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->whereIn('status', ['submitted', 'expired'])
            ->with(['user:id,name,email', 'answers:id,exam_attempt_id,needs_review']);

        $total = (clone $attempts)->count();
        $pending = (clone $attempts)->whereHas('answers', fn ($query) => $query->where('needs_review', true))->count();
        $completed = (clone $attempts)->whereDoesntHave('answers', fn ($query) => $query->where('needs_review', true));
        $passed = (clone $completed)->where('passed', true)->count();
        $failed = (clone $completed)->where('passed', false)->count();
        $avgScore = (clone $completed)->avg('percentage') ?? 0;

        $distribution = [];
        foreach (['0–20' => [0, 20], '20–40' => [20, 40], '40–60' => [40, 60], '60–80' => [60, 80], '80–100' => [80, 101]] as $label => [$min, $max]) {
            $distribution[$label] = (clone $completed)
                ->where('percentage', '>=', $min)
                ->where('percentage', '<', $max)
                ->count();
        }

        $list = (clone $attempts)
            ->orderByDesc('percentage')
            ->limit(50)
            ->get();

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'pending' => $pending,
            'pass_rate' => ($passed + $failed) > 0 ? round($passed / ($passed + $failed) * 100, 1) : 0,
            'avg_score' => round($avgScore, 1),
            'distribution' => $distribution,
            'list' => $list,
        ];
    }

    public function gradingData(ExamAttempt $attempt): array
    {
        $attempt->load(['user:id,name,email', 'exam:id,title,total_points,passing_score', 'answers.question']);

        return [
            'attempt' => $attempt,
            'manualAnswers' => $attempt->answers->filter(fn (ExamAttemptAnswer $answer) => $answer->question?->type === 'essay'),
        ];
    }

    public function gradeAttempt(ExamAttempt $attempt, array $grades, User $teacher): ExamAttempt
    {
        return DB::transaction(function () use ($attempt, $grades, $teacher): ExamAttempt {
            $attempt = ExamAttempt::query()->lockForUpdate()->with(['exam', 'answers.question'])->findOrFail($attempt->id);

            foreach ($grades as $answerId => $grade) {
                $answer = $attempt->answers->firstWhere('id', (int) $answerId);
                if (! $answer || $answer->question?->type !== 'essay') {
                    continue;
                }

                $points = min((float) $grade['points'], (float) $answer->question->points);
                $answer->forceFill([
                    'points_awarded' => $points,
                    'is_correct' => $points >= (float) $answer->question->points,
                    'needs_review' => false,
                    'feedback' => $grade['feedback'] ?? null,
                    'graded_by' => $teacher->getAuthIdentifier(),
                    'graded_at' => now(),
                ])->save();
            }

            $attempt->load('answers');
            $score = (float) $attempt->answers->sum('points_awarded');
            $maxScore = (float) $attempt->exam->total_points;
            $percentage = $maxScore > 0 ? round($score / $maxScore * 100, 2) : 0;
            $pendingReview = $attempt->answers->contains(fn (ExamAttemptAnswer $answer) => $answer->needs_review);

            $attempt->forceFill([
                'score' => $score,
                'max_score' => $maxScore,
                'percentage' => $percentage,
                'passed' => $pendingReview ? null : $score >= (float) $attempt->exam->passing_score,
                'graded_by' => $pendingReview ? null : $teacher->getAuthIdentifier(),
                'graded_at' => $pendingReview ? null : now(),
            ])->save();

            return $attempt->fresh(['answers.question', 'user']);
        });
    }
}
