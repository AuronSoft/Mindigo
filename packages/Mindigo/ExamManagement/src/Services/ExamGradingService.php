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
        $this->ensureGrader($session, $teacher);

        return $session->attempts()
            ->whereIn('status', [ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED, ExamSessionAttempt::STATUS_TERMINATED])
            ->with(['candidate', 'user', 'appeals'])
            ->orderByDesc('needs_review')
            ->orderByDesc('submitted_at')
            ->paginate(20);
    }

    public function summary(ExamSession $session, User $teacher): array
    {
        $this->ensureGrader($session, $teacher);
        $attempts = $session->attempts()->whereIn('status', [ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED, ExamSessionAttempt::STATUS_TERMINATED]);

        return [
            'submissions' => (clone $attempts)->count(),
            'pending' => (clone $attempts)->where('needs_review', true)->count(),
            'completed' => (clone $attempts)->where('grading_status', ExamSessionAttempt::GRADING_COMPLETED)->count(),
            'released' => (clone $attempts)->whereNotNull('released_at')->count(),
            'appeals' => (clone $attempts)->whereHas('appeals', fn ($query) => $query->where('status', 'open'))->count(),
        ];
    }

    public function reviewWorkspace(ExamSessionAttempt $attempt, User $teacher): ExamSessionAttempt
    {
        $this->ensureGrader($attempt->session, $teacher);

        return $attempt->load([
            'candidate',
            'user',
            'session.version.template',
            'answers' => fn ($query) => $query->with(['question', 'revisions.editor'])->orderBy('id'),
            'appeals',
            'proctorEvents' => fn ($query) => $query->with('actor')->latest('occurred_at')->limit(100),
        ]);
    }

    public function grade(
        ExamSessionAttempt $attempt,
        ExamSessionAttemptAnswer $answer,
        User $teacher,
        float $points,
        ?string $feedback,
        array $rubricScores = [],
        ?string $reason = null,
    ): ExamSessionAttempt {
        return DB::transaction(function () use ($attempt, $answer, $teacher, $points, $feedback, $rubricScores, $reason): ExamSessionAttempt {
            $attempt = ExamSessionAttempt::query()->lockForUpdate()->with('session')->findOrFail($attempt->id);
            $this->ensureGrader($attempt->session, $teacher);
            $answer = ExamSessionAttemptAnswer::query()->lockForUpdate()->with('question')->findOrFail($answer->id);

            if ((int) $answer->exam_session_attempt_id !== (int) $attempt->id) {
                throw ValidationException::withMessages(['answer' => __('Mindigo-exam-management::app.grading.invalid_answer')]);
            }

            $maximum = (float) $answer->question->points;
            if ($rubricScores !== []) {
                if (count($rubricScores) !== count($answer->question->rubric ?? [])) {
                    throw ValidationException::withMessages(['rubric_scores' => __('Mindigo-exam-management::app.grading.invalid_rubric')]);
                }
                foreach ($answer->question->rubric ?? [] as $index => $criterion) {
                    if ((float) ($rubricScores[$index] ?? 0) > (float) ($criterion['max_points'] ?? 0)) {
                        throw ValidationException::withMessages(['rubric_scores' => __('Mindigo-exam-management::app.grading.invalid_rubric')]);
                    }
                }
                $points = (float) collect($rubricScores)->sum();
            }
            if ($points > $maximum) {
                throw ValidationException::withMessages(['points_awarded' => __('Mindigo-exam-management::app.grading.points_exceeded', ['points' => $maximum])]);
            }

            $oldValues = $answer->only(['points_awarded', 'feedback', 'rubric_scores', 'needs_review', 'reviewed_by', 'reviewed_at']);
            $answer->update([
                'points_awarded' => $points,
                'is_correct' => $points >= $maximum,
                'needs_review' => false,
                'feedback' => filled($feedback) ? trim($feedback) : null,
                'rubric_scores' => $rubricScores ?: null,
                'reviewed_by' => $teacher->getAuthIdentifier(),
                'reviewed_at' => now(),
            ]);
            $answer->revisions()->create([
                'changed_by' => $teacher->getAuthIdentifier(),
                'previous_points' => $oldValues['points_awarded'] ?? 0,
                'new_points' => $points,
                'previous_feedback' => $oldValues['feedback'] ?? null,
                'new_feedback' => $answer->feedback,
                'previous_rubric_scores' => $oldValues['rubric_scores'] ?? null,
                'new_rubric_scores' => $answer->rubric_scores,
                'reason' => $reason,
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
            $this->ensureGrader($attempt->session, $teacher);

            if ($attempt->needs_review || $attempt->answers->contains('needs_review', true)) {
                throw ValidationException::withMessages(['attempt' => __('Mindigo-exam-management::app.grading.review_required')]);
            }

            if (! in_array($attempt->status, [ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED, ExamSessionAttempt::STATUS_TERMINATED], true)) {
                throw ValidationException::withMessages(['attempt' => __('Mindigo-exam-management::app.grading.not_releasable')]);
            }

            if (! $attempt->released_at) {
                $oldValues = $attempt->only(['released_by', 'released_at']);
                $attempt->update([
                    'released_by' => $teacher->getAuthIdentifier(),
                    'released_at' => now(),
                    'grading_status' => ExamSessionAttempt::GRADING_RELEASED,
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
            'grading_status' => $needsReview ? ExamSessionAttempt::GRADING_PENDING_MANUAL : ExamSessionAttempt::GRADING_COMPLETED,
            'reviewed_by' => $needsReview ? null : $teacher->getAuthIdentifier(),
            'reviewed_at' => $needsReview ? null : now(),
        ]);

        if ($attempt->session->status === ExamSession::STATUS_ENDED) {
            $attempt->session->update(['status' => ExamSession::STATUS_GRADING]);
        }

        return $attempt->fresh(['answers.question', 'session']);
    }

    public function ensureGrader(ExamSession $session, User $teacher): void
    {
        if (! $teacher->isTeacher() || ((int) $session->organizer_id !== (int) $teacher->getAuthIdentifier() && ! $session->gradingAssignments()->where('grader_id', $teacher->getAuthIdentifier())->exists())) {
            throw new AuthorizationException;
        }
    }
}
