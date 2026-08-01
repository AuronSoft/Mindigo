<?php

namespace Mindigo\TeacherExam\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\ExamManagement\Services\ExamAuditService;
use Mindigo\ExamManagement\Services\ExamService;
use Mindigo\Notification\Notifications\ExamAssigned;
use Mindigo\TeacherExam\Http\Requests\TeacherExamRequest;

class TeacherExamService
{
    public function __construct(
        private readonly ExamService $exams,
        private readonly ExamAuditService $audit,
    ) {}

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
        $this->notifyAssignedStudents($exam->fresh());
    }

    public function notifyAssignedStudents(Exam $exam): void
    {
        if ($exam->assignment_notified_at) {
            return;
        }

        DB::transaction(function () use ($exam): void {
            $lockedExam = Exam::query()->lockForUpdate()->findOrFail($exam->id);
            if ($lockedExam->assignment_notified_at) {
                return;
            }

            $studentIds = DB::table('classroom_students')
                ->whereIn('classroom_id', $this->audienceClassroomIds($lockedExam))
                ->where('status', 'active')
                ->pluck('student_id')
                ->unique();

            if ($studentIds->isNotEmpty()) {
                $students = User::query()->whereIn('id', $studentIds)->where('role', 'student')->get();
                Notification::send($students, new ExamAssigned(
                    examId: $lockedExam->id,
                    examTitle: $lockedExam->title,
                    teacherName: $lockedExam->creator()->value('name'),
                    startsAt: $lockedExam->starts_at?->format('d/m/Y H:i'),
                    url: Route::has('student.exams.index') ? route('student.exams.index') : '/student/exams',
                ));
            }

            $lockedExam->forceFill(['assignment_notified_at' => now()])->save();
        });
    }

    public function monitoringData(Exam $exam, User $teacher, array $filters = []): array
    {
        $classrooms = Classroom::query()
            ->whereIn('id', $this->audienceClassroomIds($exam))
            ->when(! $teacher->isAdmin(), fn ($query) => $query->where('teacher_id', $teacher->getAuthIdentifier()))
            ->where('status', 'active')
            ->with(['students' => fn ($query) => $query
                ->where('classroom_students.status', 'active')
                ->select('users.id', 'users.name', 'users.email')])
            ->orderBy('name')
            ->get();

        $selectedClassroom = filled($filters['classroom'] ?? null) ? (int) $filters['classroom'] : null;
        abort_if($selectedClassroom && ! $classrooms->contains('id', $selectedClassroom), 403);

        $allStudentIds = $classrooms
            ->flatMap(fn (Classroom $classroom) => $classroom->students->pluck('id'))
            ->unique()
            ->values();
        $studentIds = $selectedClassroom
            ? $classrooms->firstWhere('id', $selectedClassroom)->students->pluck('id')->unique()->values()
            : $allStudentIds;

        $attempts = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->whereIn('user_id', $allStudentIds)
            ->with(['user:id,name,email', 'answers:id,exam_attempt_id,answer,needs_review'])
            ->latest('id')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $students = User::query()->whereIn('id', $studentIds)->get(['id', 'name', 'email'])
            ->map(fn (User $student) => $this->monitoringRow($student, $attempts->get($student->id), $exam));

        if (filled($filters['search'] ?? null)) {
            $term = mb_strtolower(trim((string) $filters['search']));
            $students = $students->filter(fn (array $row) => str_contains(mb_strtolower($row['name'].' '.$row['email']), $term));
        }
        if (filled($filters['status'] ?? null)) {
            $students = $students->where('status', $filters['status']);
        }

        $sort = $filters['sort'] ?? 'name';
        $direction = $filters['direction'] ?? 'asc';
        $students = $students->sortBy($sort, SORT_NATURAL | SORT_FLAG_CASE, $direction === 'desc')->values();

        return [
            'classrooms' => $classrooms,
            'classroomStats' => $classrooms->map(fn (Classroom $classroom) => $this->classroomStats($classroom, $attempts))->values(),
            'summary' => $this->summary($students),
            'students' => $this->paginate($students, 20),
            'filters' => $filters,
        ];
    }

    private function monitoringRow(User $student, ?ExamAttempt $attempt, Exam $exam): array
    {
        $answered = $attempt?->answers->filter(fn (ExamAttemptAnswer $answer) => filled($answer->answer))->count() ?? 0;
        $totalQuestions = max(1, (int) $exam->total_questions);
        $lastActivity = $attempt?->last_activity_at ?? $attempt?->updated_at;
        $status = $attempt?->status ?? 'not_started';

        return [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'attempt_id' => $attempt?->id,
            'status' => $status,
            'online' => $status === 'in_progress' && $lastActivity?->gte(now()->subSeconds(45)),
            'answered' => $answered,
            'total_questions' => $totalQuestions,
            'progress' => min(100, round($answered / $totalQuestions * 100)),
            'remaining' => $status === 'in_progress' && $attempt?->expires_at ? max(0, now()->diffInSeconds($attempt->expires_at, false)) : null,
            'last_activity' => $lastActivity?->toIso8601String(),
            'last_activity_label' => $lastActivity?->diffForHumans(),
            'score' => in_array($status, ['submitted', 'expired'], true) ? $attempt?->percentage : null,
            'grading_complete' => $attempt && in_array($status, ['submitted', 'expired'], true)
                ? ! $attempt->answers->contains(fn (ExamAttemptAnswer $answer) => $answer->needs_review)
                : false,
        ];
    }

    private function classroomStats(Classroom $classroom, Collection $attempts): array
    {
        $classAttempts = $classroom->students->map(fn (User $student) => $attempts->get($student->id))->filter();
        $submitted = $classAttempts->whereIn('status', ['submitted', 'expired']);
        $graded = $submitted->filter(fn (ExamAttempt $attempt) => ! $attempt->answers->contains(fn (ExamAttemptAnswer $answer) => $answer->needs_review));
        $assigned = $classroom->students->count();

        return [
            'id' => $classroom->id,
            'name' => $classroom->name,
            'assigned' => $assigned,
            'started' => $classAttempts->count(),
            'submitted' => $submitted->count(),
            'completion_rate' => $assigned > 0 ? round($submitted->count() / $assigned * 100, 1) : 0,
            'average_score' => round((float) ($graded->avg('percentage') ?? 0), 1),
            'highest_score' => $graded->isEmpty() ? null : round((float) $graded->max('percentage'), 1),
            'lowest_score' => $graded->isEmpty() ? null : round((float) $graded->min('percentage'), 1),
            'grading_progress' => $submitted->isEmpty() ? 0 : round($graded->count() / $submitted->count() * 100, 1),
        ];
    }

    private function summary(Collection $students): array
    {
        $submitted = $students->whereIn('status', ['submitted', 'expired']);

        return [
            'assigned' => $students->count(),
            'started' => $students->where('status', '!=', 'not_started')->count(),
            'in_progress' => $students->where('status', 'in_progress')->count(),
            'submitted' => $submitted->count(),
            'online' => $students->where('online', true)->count(),
        ];
    }

    private function audienceClassroomIds(Exam $exam): array
    {
        return array_values(array_unique(array_map('intval', $exam->audience['classrooms'] ?? [])));
    }

    private function paginate(Collection $items, int $perPage): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();

        return new Paginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()]
        );
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
            ->paginate(25)
            ->withQueryString();

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

    public function gradeAttempt(ExamAttempt $attempt, array $grades, User $teacher, ?int $expectedVersion = null): ExamAttempt
    {
        $gradedAttempt = DB::transaction(function () use ($attempt, $grades, $teacher, $expectedVersion): ExamAttempt {
            $attempt = ExamAttempt::query()->lockForUpdate()->with(['exam', 'answers.question'])->findOrFail($attempt->id);

            if ($expectedVersion !== null && $attempt->grading_version !== $expectedVersion) {
                throw ValidationException::withMessages([
                    'grading_version' => __('teacher-exam::app.stale_grading'),
                ]);
            }

            if (! in_array($attempt->status, ['submitted', 'expired'], true)) {
                throw ValidationException::withMessages([
                    'grades' => __('teacher-exam::app.attempt_not_gradable'),
                ]);
            }
            if (! $teacher->isAdmin() && (int) $attempt->exam->created_by !== (int) $teacher->getAuthIdentifier()) {
                throw ValidationException::withMessages([
                    'grades' => __('teacher-exam::app.unauthorized_exam'),
                ]);
            }

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
                'grading_version' => $attempt->grading_version + 1,
            ])->save();

            return $attempt->fresh(['answers.question', 'user']);
        });

        $this->audit->record(
            'grade',
            'exam_attempts',
            [],
            ['score' => $gradedAttempt->score, 'grading_version' => $gradedAttempt->grading_version],
            ['attempt_id' => $gradedAttempt->id, 'exam_id' => $gradedAttempt->exam_id],
            $gradedAttempt
        );

        return $gradedAttempt;
    }
}
