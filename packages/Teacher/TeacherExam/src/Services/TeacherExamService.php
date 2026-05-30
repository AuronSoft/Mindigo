<?php

namespace Mindigo\TeacherExam\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
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
            'total'     => (clone $base)->count(),
            'published' => (clone $base)->where('status', 'published')->count(),
            'draft'     => (clone $base)->where('status', 'draft')->count(),
            'closed'    => (clone $base)->where('status', 'closed')->count(),
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

    public function formData(): array
    {
        return $this->exams->formData();
    }

    /**
     * Kết quả chi tiết của một đề thi (cho teacher xem).
     */
    public function examResults(Exam $exam): array
    {
        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->where('status', 'submitted')
            ->with('user:id,name,email');

        $total      = (clone $attempts)->count();
        $passed     = (clone $attempts)->where('passed', true)->count();
        $avgScore   = (clone $attempts)->avg('percentage') ?? 0;

        $distribution = [];
        foreach (['0–20' => [0,20], '20–40' => [20,40], '40–60' => [40,60], '60–80' => [60,80], '80–100' => [80,101]] as $label => [$min, $max]) {
            $distribution[$label] = (clone $attempts)
                ->where('percentage', '>=', $min)
                ->where('percentage', '<', $max)
                ->count();
        }

        $list = (clone $attempts)
            ->orderByDesc('percentage')
            ->limit(50)
            ->get();

        return [
            'total'        => $total,
            'passed'       => $passed,
            'failed'       => $total - $passed,
            'pass_rate'    => $total > 0 ? round($passed / $total * 100, 1) : 0,
            'avg_score'    => round($avgScore, 1),
            'distribution' => $distribution,
            'list'         => $list,
        ];
    }
}
