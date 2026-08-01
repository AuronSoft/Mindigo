<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamQuestion;

class ExamAttemptService
{
    public function __construct(private ExamAuditService $audit) {}

    public function start(Exam $exam, User $user): ExamAttempt
    {
        $attempt = DB::transaction(function () use ($exam, $user): ExamAttempt {
            $exam = Exam::query()->lockForUpdate()->findOrFail($exam->id);
            if (! $exam->isOpen()) {
                throw ValidationException::withMessages(['exam' => __('Mindigo-exam-management::app.messages.exam_not_open')]);
            }
            if ($user->isStudent() && ! $this->isAssignedToStudent($exam, $user)) {
                throw new AuthorizationException(__('Mindigo-exam-management::app.messages.not_assigned'));
            }

            $attempt = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('user_id', $user->getAuthIdentifier())
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->first();

            if ($attempt) {
                return $attempt;
            }

            if ($this->submittedAttemptCount($exam, $user) >= $exam->max_attempts) {
                throw ValidationException::withMessages(['exam' => __('Mindigo-exam-management::app.messages.max_attempts_reached')]);
            }

            $questionIds = $exam->questions()->pluck('id')->all();
            if ($exam->shuffle_questions) {
                shuffle($questionIds);
            }

            return ExamAttempt::query()->create([
                'exam_id' => $exam->id,
                'user_id' => $user->getAuthIdentifier(),
                'status' => 'in_progress',
                'started_at' => now(),
                'expires_at' => now()->addMinutes($exam->duration_minutes),
                'max_score' => $exam->total_points,
                'question_order' => $questionIds,
            ]);
        });

        $this->auditAttempt('start', [], ['attempt_id' => $attempt->id], $attempt);

        return $attempt;
    }

    public function submittedAttemptCount(Exam $exam, User $user): int
    {
        return ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->whereIn('status', ['submitted', 'expired'])
            ->count();
    }

    public function orderedQuestions(ExamAttempt $attempt): Collection
    {
        $questions = ExamQuestion::query()
            ->whereIn('id', $attempt->question_order ?? [])
            ->get()
            ->keyBy('id');

        return collect($attempt->question_order ?? [])
            ->map(fn ($id) => $questions->get($id))
            ->filter()
            ->values();
    }

    public function autosave(ExamAttempt $attempt, array $answers): bool
    {
        return DB::transaction(function () use ($attempt, $answers): bool {
            $attempt = ExamAttempt::query()->lockForUpdate()->findOrFail($attempt->id);
            if ($attempt->status !== 'in_progress' || ($attempt->expires_at && now()->gte($attempt->expires_at))) {
                return false;
            }

            $attempt->forceFill(['autosave_payload' => ['answers' => $answers]])->save();

            return true;
        });
    }

    public function logViolation(ExamAttempt $attempt): int
    {
        if ($attempt->status === 'in_progress') {
            $attempt->increment('tab_leave_count');
        }

        return (int) $attempt->fresh()->tab_leave_count;
    }

    public function finalize(ExamAttempt $attempt, array $answers, string $status): void
    {
        DB::transaction(function () use ($attempt, $answers, $status): void {
            $attempt = ExamAttempt::query()->lockForUpdate()->with('exam.questions')->findOrFail($attempt->id);
            if ($attempt->status !== 'in_progress') {
                return;
            }

            if ($attempt->expires_at && now()->gte($attempt->expires_at)) {
                $status = 'expired';
                $answers = $attempt->autosave_payload['answers'] ?? [];
            }
            $score = 0.0;
            $pendingReview = false;

            foreach ($attempt->exam->questions as $question) {
                $answer = $this->normalizeAnswer($answers[$question->id] ?? $answers[(string) $question->id] ?? null);
                [$isCorrect, $points, $needsReview] = $this->scoreAnswer($question, $answer);
                $score += $points;
                $pendingReview = $pendingReview || $needsReview;

                ExamAttemptAnswer::query()->updateOrCreate(
                    [
                        'exam_attempt_id' => $attempt->id,
                        'exam_question_id' => $question->id,
                    ],
                    [
                        'type' => $question->type,
                        'answer' => $answer,
                        'is_correct' => $isCorrect,
                        'points_awarded' => $points,
                        'needs_review' => $needsReview,
                    ]
                );
            }

            $maxScore = (float) $attempt->exam->total_points;
            $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0;

            $attempt->forceFill([
                'status' => $status,
                'submitted_at' => now(),
                'score' => $score,
                'max_score' => $maxScore,
                'percentage' => $percentage,
                'passed' => $pendingReview ? null : $score >= (float) $attempt->exam->passing_score,
                'autosave_payload' => ['answers' => $answers],
            ])->save();
        });

        $this->auditAttempt($status, [], ['attempt_id' => $attempt->id, 'score' => $attempt->fresh()->score], $attempt);
    }

    public function canViewAttempt(User $user, ExamAttempt $attempt, bool $allowStaff = false): bool
    {
        if ($allowStaff && ! $user->isStudent() && $user->hasPermissionTo('exams.view')) {
            return $user->isAdmin()
                || (int) $attempt->exam()->withTrashed()->value('created_by') === (int) $user->getAuthIdentifier();
        }

        return (int) $attempt->user_id === (int) $user->getAuthIdentifier();
    }

    private function scoreAnswer(ExamQuestion $question, array $answer): array
    {
        if ($question->type === 'essay') {
            return [null, 0.0, true];
        }

        $expected = $this->normalizeComparable($question->correct_answers ?? []);
        $actual = $this->normalizeComparable($answer);
        $isCorrect = $expected === $actual && ! empty($expected);

        return [$isCorrect, $isCorrect ? (float) $question->points : 0.0, false];
    }

    private function normalizeAnswer(mixed $answer): array
    {
        return collect((array) $answer)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all();
    }

    private function normalizeComparable(array $answer): array
    {
        return collect($answer)
            ->map(fn ($value) => mb_strtolower(trim((string) $value)))
            ->filter()
            ->sort()
            ->values()
            ->all();
    }

    private function isAssignedToStudent(Exam $exam, User $student): bool
    {
        $classroomIds = array_map('intval', $exam->audience['classrooms'] ?? []);
        if ($classroomIds === []) {
            return false;
        }

        return DB::table('classroom_students')
            ->whereIn('classroom_id', $classroomIds)
            ->where('student_id', $student->getAuthIdentifier())
            ->where('status', 'active')
            ->exists();
    }

    private function auditAttempt(string $action, array $oldValues, array $newValues, ExamAttempt $attempt): void
    {
        $this->audit->record(
            $action,
            'exam_attempts',
            $oldValues,
            $newValues,
            ['attempt_id' => $attempt->id, 'exam_id' => $attempt->exam_id],
            $attempt
        );
    }
}
