<?php

namespace Mindigo\StudentExam\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamQuestion;
use Mindigo\ExamManagement\Services\ExamAuditService;
use Mindigo\TeacherClassroom\Models\Classroom;

class ExamService
{
    public function __construct(private readonly ExamAuditService $audit) {}

    public function classroomIdsForStudent(int|string $studentId): Collection
    {
        return Classroom::query()
            ->whereHas('students', function ($q) use ($studentId) {
                $q->where('student_id', $studentId)
                    ->where('classroom_students.status', 'active');
            })
            ->pluck('id');
    }

    public function getExamsForStudent(int|string $studentId): array
    {
        $classroomIds = $this->classroomIdsForStudent($studentId);

        if ($classroomIds->isEmpty()) {
            return ['upcoming' => collect(), 'ongoing' => collect(), 'completed' => collect()];
        }

        $exams = Exam::query()
            ->where('status', 'published')
            ->whereJsonContains('audience->roles', 'student')
            ->where(function ($query) use ($classroomIds) {
                foreach ($classroomIds as $classroomId) {
                    $query->orWhereJsonContains('audience->classrooms', (int) $classroomId);
                }
            })
            ->with(['attempts' => fn ($q) => $q->where('user_id', $studentId)])
            ->orderBy('starts_at')
            ->get();

        $upcoming = collect();
        $ongoing = collect();
        $completed = collect();

        $now = now();

        foreach ($exams as $exam) {
            $myAttempts = $exam->attempts;
            $attemptCount = $myAttempts->whereIn('status', ['submitted', 'expired'])->count();
            $hasActiveAttempt = $myAttempts->contains('status', 'in_progress');
            $maxAttempts = $exam->max_attempts ?? 1;

            if ($exam->starts_at && $now->lt($exam->starts_at)) {
                $upcoming->push($exam);
            } elseif ($exam->ends_at && $now->gt($exam->ends_at)) {
                $completed->push($exam);
            } elseif ($hasActiveAttempt) {
                $ongoing->push($exam);
            } elseif ($attemptCount >= $maxAttempts) {
                $completed->push($exam);
            } else {
                $ongoing->push($exam);
            }
        }

        return compact('upcoming', 'ongoing', 'completed');
    }

    public function isEnrolledInExamClassroom(Exam $exam, int|string $studentId): bool
    {
        if (! $exam->audience) {
            return false;
        }

        $classrooms = $exam->audience['classrooms'] ?? [];
        if (empty($classrooms)) {
            return false;
        }

        return $this->classroomIdsForStudent($studentId)
            ->intersect($classrooms)
            ->isNotEmpty();
    }

    public function isAvailable(Exam $exam): bool
    {
        if ($exam->status !== 'published') {
            return false;
        }

        $now = now();

        if ($exam->starts_at && $now->lt($exam->starts_at)) {
            return false;
        }

        if ($exam->ends_at && $now->gt($exam->ends_at)) {
            return false;
        }

        return true;
    }

    public function hasExceededAttempts(Exam $exam, int|string $studentId): bool
    {
        $maxAttempts = $exam->max_attempts ?? 1;

        $done = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $studentId)
            ->whereIn('status', ['submitted', 'expired'])
            ->count();

        return $done >= $maxAttempts;
    }

    public function startAttempt(Exam $exam, int|string $studentId): ExamAttempt
    {
        $attempt = DB::transaction(function () use ($exam, $studentId): ExamAttempt {
            $exam = Exam::query()->lockForUpdate()->findOrFail($exam->id);

            if (! $this->isEnrolledInExamClassroom($exam, $studentId)) {
                throw new AuthorizationException(__('student-exam::app.not_enrolled'));
            }
            if (! $this->isAvailable($exam)) {
                throw ValidationException::withMessages(['exam' => __('student-exam::app.exam_not_available')]);
            }

            $activeAttempt = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('user_id', $studentId)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->first();

            if ($activeAttempt && (! $activeAttempt->expires_at || $activeAttempt->expires_at->isFuture())) {
                return $activeAttempt;
            }

            if ($activeAttempt) {
                $this->finalizeLockedAttempt($activeAttempt, [], 'expired');
            }

            if ($this->hasExceededAttempts($exam, $studentId)) {
                throw ValidationException::withMessages(['exam' => __('student-exam::app.max_attempts_reached')]);
            }

            $questionIds = $exam->questions()->orderBy('sort_order')->pluck('id')->all();
            if ($exam->shuffle_questions) {
                shuffle($questionIds);
            }

            $expiresAt = now()->addMinutes($exam->duration_minutes);
            if ($exam->ends_at && $expiresAt->gt($exam->ends_at)) {
                $expiresAt = $exam->ends_at;
            }

            return ExamAttempt::query()->create([
                'exam_id' => $exam->id,
                'user_id' => $studentId,
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'last_activity_at' => now(),
                'status' => 'in_progress',
                'max_score' => $exam->total_points,
                'question_order' => $questionIds,
                'tab_leave_count' => 0,
            ]);
        });

        $this->auditAttempt('start', $attempt);

        return $attempt;
    }

    public function getQuestionsForAttempt(ExamAttempt $attempt): Collection
    {
        $order = $attempt->question_order ?? [];
        if (empty($order)) {
            $order = $attempt->exam->questions()->orderBy('sort_order')->pluck('id')->all();
            $attempt->forceFill(['question_order' => $order])->save();
        }

        $questions = ExamQuestion::query()->whereIn('id', $order)->get()->keyBy('id');

        return collect($order)->map(fn ($id) => $questions->get($id))->filter()->values();
    }

    public function getSavedAnswers(ExamAttempt $attempt): Collection
    {
        return ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)
            ->get()
            ->keyBy('exam_question_id');
    }

    public function saveAnswer(ExamAttempt $attempt, int $questionId, mixed $answer): bool
    {
        return DB::transaction(function () use ($attempt, $questionId, $answer): bool {
            $attempt = ExamAttempt::query()->lockForUpdate()->with('exam')->findOrFail($attempt->id);
            if (! $attempt->exam || $attempt->status !== 'in_progress') {
                return false;
            }
            if ($attempt->expires_at && now()->gte($attempt->expires_at)) {
                $this->finalizeLockedAttempt($attempt, [], 'expired');

                return false;
            }

            $question = ExamQuestion::query()
                ->where('exam_id', $attempt->exam_id)
                ->findOrFail($questionId);

            ExamAttemptAnswer::query()->updateOrCreate(
                ['exam_attempt_id' => $attempt->id, 'exam_question_id' => $question->id],
                ['type' => $question->type, 'answer' => $this->normalizeAnswer($answer)]
            );

            $attempt->forceFill(['last_activity_at' => now()])->saveQuietly();

            return true;
        });
    }

    public function recordActivity(ExamAttempt $attempt): bool
    {
        return ExamAttempt::query()
            ->whereKey($attempt->id)
            ->where('status', 'in_progress')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->update(['last_activity_at' => now(), 'updated_at' => now()]) === 1;
    }

    public function submitAttempt(ExamAttempt $attempt, array $validated, string $status = 'submitted'): ExamAttempt
    {
        $attempt = DB::transaction(function () use ($attempt, $validated, $status): ExamAttempt {
            $attempt = ExamAttempt::query()
                ->lockForUpdate()
                ->with('exam.questions')
                ->findOrFail($attempt->id);

            if (! $attempt->exam) {
                throw ValidationException::withMessages(['exam' => __('student-exam::app.exam_not_available')]);
            }

            if ($attempt->status !== 'in_progress') {
                throw ValidationException::withMessages(['attempt' => __('student-exam::app.already_submitted')]);
            }

            $expired = $attempt->expires_at && now()->gte($attempt->expires_at);
            $finalStatus = $expired ? 'expired' : $status;
            $answers = $expired || $status === 'expired' ? [] : ($validated['answers'] ?? []);
            $finalPayload = $validated;
            if ($expired || $status === 'expired') {
                unset($finalPayload['answers']);
            }

            return $this->finalizeLockedAttempt($attempt, $answers, $finalStatus, $finalPayload);
        });

        $this->auditAttempt($attempt->status, $attempt);

        return $attempt;
    }

    private function finalizeLockedAttempt(ExamAttempt $attempt, array $answers, string $status, array $validated = []): ExamAttempt
    {
        $answers = $validated['answers'] ?? $answers;
        $tabLeaveCount = $validated['tab_leave_count'] ?? $attempt->tab_leave_count;

        // Lưu từng đáp án (upsert)
        foreach ($answers as $questionId => $value) {
            ExamAttemptAnswer::updateOrCreate(
                ['exam_attempt_id' => $attempt->id, 'exam_question_id' => $questionId],
                ['type' => $attempt->exam->questions->firstWhere('id', (int) $questionId)?->type
                    ?? throw ValidationException::withMessages(["answers.{$questionId}" => __('student-exam::app.invalid_question')]),
                    'answer' => $this->normalizeAnswer($value)]
            );
        }

        // Chấm điểm tự động
        [$score, $maxScore, $pendingReview] = $this->autoGrade($attempt);

        $attempt->update([
            'submitted_at' => now(),
            'status' => $status,
            'tab_leave_count' => $tabLeaveCount,
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => $maxScore > 0 ? round($score / $maxScore * 100, 2) : 0,
            'passed' => $pendingReview ? null : $score >= (float) $attempt->exam->passing_score,
        ]);

        return $attempt->fresh();
    }

    public function autoSubmit(ExamAttempt $attempt): void
    {
        if (in_array($attempt->status, ['submitted', 'expired'])) {
            return;
        }

        try {
            $this->submitAttempt($attempt, ['tab_leave_count' => $attempt->tab_leave_count], 'expired');
        } catch (ValidationException) {
            // Another request finalized the attempt while this request was waiting for the row lock.
        }
    }

    private function autoGrade(ExamAttempt $attempt): array
    {
        $exam = $attempt->exam()->with('questions')->firstOrFail();
        $questions = $exam->questions;
        $score = 0;
        $maxScore = 0;
        $pendingReview = false;

        foreach ($questions as $question) {
            $point = $question->points ?? 1;
            $maxScore += $point;

            $answer = ExamAttemptAnswer::query()->firstOrCreate(
                ['exam_attempt_id' => $attempt->id, 'exam_question_id' => $question->id],
                ['type' => $question->type, 'answer' => []]
            );
            $given = $answer->answer ?? [];

            if ($question->type === 'essay') {
                $answer->forceFill(['is_correct' => null, 'points_awarded' => 0, 'needs_review' => true])->save();
                $pendingReview = true;

                continue;
            }

            // Lấy các option đúng
            $correctOptions = collect($question->correct_answers ?? [])->sort()->values();

            if ($correctOptions->isEmpty()) {
                $answer->forceFill(['is_correct' => false, 'points_awarded' => 0, 'needs_review' => false])->save();

                continue;
            }

            // So sánh đáp án
            $givenIds = collect($given)->map(fn ($value) => (string) $value)->sort()->values();
            $correctOptions = $correctOptions->map(fn ($value) => (string) $value)->sort()->values();

            $isCorrect = $givenIds->toArray() === $correctOptions->toArray();

            if ($isCorrect) {
                $score += $point;
            }

            $answer->forceFill([
                'is_correct' => $isCorrect,
                'points_awarded' => $isCorrect ? $point : 0,
                'needs_review' => false,
            ])->save();
        }

        return [$score, $maxScore, $pendingReview];
    }

    private function normalizeAnswer(mixed $answer): array
    {
        if (is_string($answer) && str_starts_with(trim($answer), '[')) {
            $decoded = json_decode($answer, true);
            $answer = is_array($decoded) ? $decoded : [$answer];
        }

        return collect(is_array($answer) ? $answer : [$answer])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (string) $value)
            ->values()
            ->all();
    }

    public function getResult(ExamAttempt $attempt): array
    {
        $exam = $attempt->exam()->with('questions')->firstOrFail();

        $answers = ExamAttemptAnswer::where('exam_attempt_id', $attempt->id)->get()->keyBy('exam_question_id');

        // Chỉ show review nếu đề cho phép
        $pendingReview = $answers->contains(fn (ExamAttemptAnswer $answer) => $answer->needs_review);
        $showReview = (bool) $exam->show_results && ! $pendingReview;

        return [
            'exam' => $exam,
            'score' => $pendingReview ? null : $attempt->score,
            'max_score' => $attempt->max_score,
            'percentage' => $pendingReview ? null : $attempt->percentage,
            'passed' => $pendingReview ? null : $attempt->passed,
            'pending_review' => $pendingReview,
            'show_review' => $showReview,
            'questions' => $showReview ? $exam->questions : collect(),
            'answers' => $showReview ? $answers : collect(),
        ];
    }

    private function auditAttempt(string $action, ExamAttempt $attempt): void
    {
        $this->audit->record(
            $action,
            'exam_attempts',
            [],
            ['status' => $attempt->status, 'score' => $attempt->score],
            ['attempt_id' => $attempt->id, 'exam_id' => $attempt->exam_id],
            $attempt
        );
    }
}
