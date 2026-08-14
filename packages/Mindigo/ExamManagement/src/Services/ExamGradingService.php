<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\Notification\Notifications\ExamResultReleased;

class ExamGradingService
{
    public function __construct(private readonly ExamAuditService $audit) {}

    public function attempts(ExamSession $session, User $teacher): LengthAwarePaginator
    {
        $this->ensureOrganizer($session, $teacher);

        return $session->attempts()
            ->whereIn('status', [ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED])
            ->with(['candidate', 'user'])
            ->orderByDesc('needs_review')
            ->orderByDesc('submitted_at')
            ->paginate(20);
    }

    public function summary(ExamSession $session, User $teacher): array
    {
        $this->ensureOrganizer($session, $teacher);
        $attempts = $session->attempts()->whereIn('status', [ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED]);

        return [
            'submissions' => (clone $attempts)->count(),
            'pending' => (clone $attempts)->where('needs_review', true)->count(),
            'released' => (clone $attempts)->whereNotNull('released_at')->count(),
        ];
    }

    public function reviewWorkspace(ExamSessionAttempt $attempt, User $teacher): ExamSessionAttempt
    {
        $this->ensureOrganizer($attempt->session, $teacher);

        return $attempt->load([
            'candidate',
            'user',
            'session.version.template',
            'answers' => fn ($query) => $query->with('question')->orderBy('id'),
            'proctorEvents' => fn ($query) => $query->with('actor')->latest('occurred_at')->limit(100),
        ]);
    }

    public function grade(
        ExamSessionAttempt $attempt,
        ExamSessionAttemptAnswer $answer,
        User $teacher,
        float $points,
        ?string $feedback,
    ): ExamSessionAttempt {
        return DB::transaction(function () use ($attempt, $answer, $teacher, $points, $feedback): ExamSessionAttempt {
            $attempt = ExamSessionAttempt::query()->lockForUpdate()->with('session')->findOrFail($attempt->id);
            $this->ensureOrganizer($attempt->session, $teacher);
            $answer = ExamSessionAttemptAnswer::query()->lockForUpdate()->with('question')->findOrFail($answer->id);

            if ((int) $answer->exam_session_attempt_id !== (int) $attempt->id || ! $answer->needs_review) {
                throw ValidationException::withMessages(['answer' => __('Mindigo-exam-management::app.grading.invalid_answer')]);
            }

            $maximum = (float) $answer->question->points;
            if ($points > $maximum) {
                throw ValidationException::withMessages(['points_awarded' => __('Mindigo-exam-management::app.grading.points_exceeded', ['points' => $maximum])]);
            }

            $oldValues = $answer->only(['points_awarded', 'feedback', 'needs_review', 'reviewed_by', 'reviewed_at']);
            $answer->update([
                'points_awarded' => $points,
                'is_correct' => $points >= $maximum,
                'needs_review' => false,
                'feedback' => filled($feedback) ? trim($feedback) : null,
                'reviewed_by' => $teacher->getAuthIdentifier(),
                'reviewed_at' => now(),
            ]);
            $this->audit->record(
                'exam_attempt_answer_graded',
                'exam_grading',
                $oldValues,
                $answer->fresh()->only(['points_awarded', 'feedback', 'needs_review', 'reviewed_by', 'reviewed_at']),
                ['exam_session_id' => $attempt->exam_session_id, 'exam_session_attempt_id' => $attempt->id],
                $answer
            );

            return $this->recalculate($attempt, $teacher);
        });
    }

    public function release(ExamSessionAttempt $attempt, User $teacher): ExamSessionAttempt
    {
        return DB::transaction(function () use ($attempt, $teacher): ExamSessionAttempt {
            $attempt = ExamSessionAttempt::query()->lockForUpdate()->with(['session', 'answers'])->findOrFail($attempt->id);
            $this->ensureOrganizer($attempt->session, $teacher);

            if ($attempt->needs_review || $attempt->answers->contains('needs_review', true)) {
                throw ValidationException::withMessages(['attempt' => __('Mindigo-exam-management::app.grading.review_required')]);
            }

            if (! in_array($attempt->status, [ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED], true)) {
                throw ValidationException::withMessages(['attempt' => __('Mindigo-exam-management::app.grading.not_releasable')]);
            }

            if (! $attempt->released_at) {
                $oldValues = $attempt->only(['released_by', 'released_at']);
                $attempt->update([
                    'released_by' => $teacher->getAuthIdentifier(),
                    'released_at' => now(),
                ]);
                $this->audit->record(
                    'exam_attempt_result_released',
                    'exam_grading',
                    $oldValues,
                    $attempt->fresh()->only(['released_by', 'released_at']),
                    ['exam_session_id' => $attempt->exam_session_id],
                    $attempt
                );
                $attempt->user()->first()?->notify(new ExamResultReleased(
                    $attempt->id,
                    $attempt->session->title,
                    route('student.exam-sessions.result', $attempt)
                ));
            }

            return $attempt->fresh();
        });
    }

    private function recalculate(ExamSessionAttempt $attempt, User $teacher): ExamSessionAttempt
    {
        $answers = $attempt->answers()->get();
        $needsReview = $answers->contains('needs_review', true);
        $score = (float) $answers->sum(fn (ExamSessionAttemptAnswer $answer) => (float) $answer->points_awarded);
        $maxScore = (float) $attempt->max_score;

        $attempt->update([
            'score' => $score,
            'percentage' => $maxScore > 0 ? round($score / $maxScore * 100, 2) : 0,
            'passed' => $needsReview ? null : $score >= (float) $attempt->session->passing_score,
            'needs_review' => $needsReview,
            'reviewed_by' => $needsReview ? null : $teacher->getAuthIdentifier(),
            'reviewed_at' => $needsReview ? null : now(),
        ]);

        if ($attempt->session->status === ExamSession::STATUS_ENDED) {
            $attempt->session->update(['status' => ExamSession::STATUS_GRADING]);
        }

        return $attempt->fresh(['answers.question', 'session']);
    }

    private function ensureOrganizer(ExamSession $session, User $teacher): void
    {
        if (! $teacher->isTeacher() || (int) $session->organizer_id !== (int) $teacher->getAuthIdentifier()) {
            throw new AuthorizationException;
        }
    }
}
