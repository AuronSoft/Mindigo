<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamQuestion;

class ExamAttemptService
{
    public function __construct(private ExamAuditService $audit)
    {
    }

    public function start(Exam $exam, User $user): ExamAttempt
    {
        $attempt = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->where('status', 'in_progress')
            ->first();

        if ($attempt) {
            return $attempt;
        }

        $questionIds = $exam->questions()->pluck('id')->all();
        if ($exam->shuffle_questions) {
            shuffle($questionIds);
        }

        $attempt = ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $user->getAuthIdentifier(),
            'status' => 'in_progress',
            'started_at' => now(),
            'expires_at' => now()->addMinutes($exam->duration_minutes),
            'max_score' => $exam->total_points,
            'question_order' => $questionIds,
        ]);

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

    public function autosave(ExamAttempt $attempt, array $answers): void
    {
        $attempt->forceFill([
            'autosave_payload' => ['answers' => $answers],
        ])->save();
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
            $attempt->load('exam.questions');
            $score = 0.0;

            foreach ($attempt->exam->questions as $question) {
                $answer = $this->normalizeAnswer($answers[$question->id] ?? $answers[(string) $question->id] ?? null);
                [$isCorrect, $points, $needsReview] = $this->scoreAnswer($question, $answer);
                $score += $points;

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
                'passed' => $score >= (float) $attempt->exam->passing_score,
                'autosave_payload' => ['answers' => $answers],
            ])->save();
        });

        $this->auditAttempt($status, [], ['attempt_id' => $attempt->id, 'score' => $attempt->fresh()->score], $attempt);
    }

    public function canViewAttempt(User $user, ExamAttempt $attempt, bool $allowStaff = false): bool
    {
        if ($allowStaff && (int) $attempt->user_id !== (int) $user->getAuthIdentifier() && $user->hasPermissionTo('exams.view') && !$user->isStudent()) {
            return true;
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
        $isCorrect = $expected === $actual && !empty($expected);

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
