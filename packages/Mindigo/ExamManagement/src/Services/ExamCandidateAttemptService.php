<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Events\ExamMonitoringUpdated;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamTemplateQuestion;

class ExamCandidateAttemptService
{
    public function workspace(User $student): array
    {
        $sessions = ExamSession::query()
            ->whereHas('candidates', fn ($query) => $query->where('user_id', $student->getAuthIdentifier())->where('status', ExamCandidate::STATUS_ELIGIBLE))
            ->with(['version.template', 'attempts' => fn ($query) => $query->where('user_id', $student->getAuthIdentifier())])
            ->orderBy('starts_at')->get();

        return [
            'upcoming' => $sessions->filter(fn (ExamSession $session) => $session->starts_at?->isFuture()),
            'available' => $sessions->filter(fn (ExamSession $session) => $this->isOpen($session)),
            'completed' => $sessions->filter(fn (ExamSession $session) => $session->ends_at?->isPast()),
        ];
    }

    public function start(ExamSession $session, User $student): ExamSessionAttempt
    {
        $attempt = DB::transaction(function () use ($session, $student): ExamSessionAttempt {
            $session = ExamSession::query()->lockForUpdate()->with('version.questions')->findOrFail($session->id);
            $candidate = ExamCandidate::query()->where('exam_session_id', $session->id)
                ->where('user_id', $student->getAuthIdentifier())->lockForUpdate()->first();

            if (! $candidate || $candidate->status !== ExamCandidate::STATUS_ELIGIBLE) {
                throw new AuthorizationException(__('Mindigo-exam-management::app.candidate_attempt.not_eligible'));
            }
            if (! $this->isOpen($session)) {
                throw ValidationException::withMessages(['session' => __('Mindigo-exam-management::app.candidate_attempt.not_open')]);
            }

            $active = ExamSessionAttempt::query()->where('exam_session_id', $session->id)
                ->where('user_id', $student->getAuthIdentifier())->where('status', ExamSessionAttempt::STATUS_IN_PROGRESS)
                ->lockForUpdate()->latest('attempt_number')->first();
            if ($active?->isActive()) {
                return $active;
            }
            if ($active) {
                $active->update(['status' => ExamSessionAttempt::STATUS_EXPIRED, 'submitted_at' => now()]);
            }

            $usedAttempts = ExamSessionAttempt::query()->where('exam_session_id', $session->id)
                ->where('user_id', $student->getAuthIdentifier())->count();
            $limit = $candidate->max_attempts_override ?? $session->max_attempts;
            if ($usedAttempts >= $limit) {
                throw ValidationException::withMessages(['session' => __('Mindigo-exam-management::app.candidate_attempt.limit_reached')]);
            }

            $questionIds = $session->version->questions->sortBy('sort_order')->pluck('id')->all();
            if ($session->shuffle_questions) {
                shuffle($questionIds);
            }
            $expiresAt = now()->addMinutes($session->duration_minutes + $candidate->extra_time_minutes);
            if ($session->ends_at && $expiresAt->gt($session->ends_at)) {
                $expiresAt = $session->ends_at;
            }

            return ExamSessionAttempt::query()->create([
                'exam_session_id' => $session->id,
                'exam_candidate_id' => $candidate->id,
                'user_id' => $student->getAuthIdentifier(),
                'attempt_number' => $usedAttempts + 1,
                'status' => ExamSessionAttempt::STATUS_IN_PROGRESS,
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'last_activity_at' => now(),
                'question_order' => $questionIds,
                'answer_order' => $this->answerOrder($session->version->questions, $session->shuffle_answers),
                'security_events' => [],
                'anonymous_code' => 'CAND-'.str()->upper(str()->random(10)),
            ]);
        });
        ExamMonitoringUpdated::dispatch($session->id, $attempt->id, 'attempt_started');

        return $attempt;
    }

    public function questions(ExamSessionAttempt $attempt, User $student): Collection
    {
        if ((int) $attempt->user_id !== (int) $student->getAuthIdentifier()) {
            throw new AuthorizationException;
        }

        $questions = ExamTemplateQuestion::query()->whereIn('id', $attempt->question_order)->get()->keyBy('id');

        return collect($attempt->question_order)->map(fn (int $id) => $questions->get($id))->filter()->values();
    }

    public function saveAnswer(ExamSessionAttempt $attempt, User $student, int $questionId, mixed $answer): bool
    {
        return DB::transaction(function () use ($attempt, $student, $questionId, $answer): bool {
            $attempt = $this->lockedOwnedAttempt($attempt, $student);
            if (! $this->keepAlive($attempt)) {
                return false;
            }

            $question = ExamTemplateQuestion::query()->whereKey($questionId)
                ->where('exam_template_version_id', $attempt->session->exam_template_version_id)->firstOrFail();
            ExamSessionAttemptAnswer::query()->updateOrCreate(
                ['exam_session_attempt_id' => $attempt->id, 'exam_template_question_id' => $question->id],
                ['type' => $question->type, 'answer' => $this->normalizeAnswer($answer)]
            );

            return true;
        });
    }

    public function heartbeat(ExamSessionAttempt $attempt, User $student): bool
    {
        return DB::transaction(function () use ($attempt, $student): bool {
            return $this->keepAlive($this->lockedOwnedAttempt($attempt, $student));
        });
    }

    public function recordSecurityEvent(ExamSessionAttempt $attempt, User $student, string $type): bool
    {
        return DB::transaction(function () use ($attempt, $student, $type): bool {
            $attempt = $this->lockedOwnedAttempt($attempt, $student);
            if (! $this->keepAlive($attempt)) {
                return false;
            }

            $events = collect($attempt->security_events ?? [])->push(['type' => $type, 'occurred_at' => now()->toIso8601String()])->take(-500)->values()->all();
            $attempt->update(['security_events' => $events]);

            return true;
        });
    }

    public function submit(ExamSessionAttempt $attempt, User $student, array $answers = []): ExamSessionAttempt
    {
        $submittedAttempt = DB::transaction(function () use ($attempt, $student, $answers): ExamSessionAttempt {
            $attempt = $this->lockedOwnedAttempt($attempt, $student);
            if ($attempt->status !== ExamSessionAttempt::STATUS_IN_PROGRESS) {
                return $attempt;
            }

            foreach ($answers as $questionId => $answer) {
                $question = ExamTemplateQuestion::query()->whereKey((int) $questionId)
                    ->where('exam_template_version_id', $attempt->session->exam_template_version_id)->firstOrFail();
                ExamSessionAttemptAnswer::query()->updateOrCreate(
                    ['exam_session_attempt_id' => $attempt->id, 'exam_template_question_id' => $question->id],
                    ['type' => $question->type, 'answer' => $this->normalizeAnswer($answer)]
                );
            }

            $this->finalize($attempt, $attempt->expires_at->isPast() ? ExamSessionAttempt::STATUS_EXPIRED : ExamSessionAttempt::STATUS_SUBMITTED);

            return $attempt->fresh(['answers.question', 'session']);
        });
        ExamMonitoringUpdated::dispatch($submittedAttempt->exam_session_id, $submittedAttempt->id, 'attempt_submitted');

        return $submittedAttempt;
    }

    private function grade(ExamSessionAttempt $attempt): array
    {
        $questions = $attempt->session->version->questions;
        $answers = $attempt->answers()->get()->keyBy('exam_template_question_id');
        $score = 0.0;
        $maxScore = 0.0;
        $needsReview = false;

        foreach ($questions as $question) {
            $points = (float) $question->points;
            $maxScore += $points;
            $answer = $answers->get($question->id) ?? ExamSessionAttemptAnswer::query()->create([
                'exam_session_attempt_id' => $attempt->id, 'exam_template_question_id' => $question->id,
                'type' => $question->type, 'answer' => [],
            ]);

            if ($question->type === 'essay') {
                $answer->update(['is_correct' => null, 'points_awarded' => 0, 'needs_review' => true]);
                $needsReview = true;

                continue;
            }

            $given = collect($answer->answer ?? [])->map(fn ($value) => mb_strtolower(trim((string) $value)))->sort()->values()->all();
            $correct = collect($question->correct_answers ?? [])->map(fn ($value) => mb_strtolower(trim((string) $value)))->sort()->values()->all();
            $isCorrect = $correct !== [] && $given === $correct;
            $awarded = $isCorrect ? $points : 0;
            $score += $awarded;
            $answer->update(['is_correct' => $isCorrect, 'points_awarded' => $awarded, 'needs_review' => false]);
        }

        return [$score, $maxScore, $needsReview];
    }

    private function lockedOwnedAttempt(ExamSessionAttempt $attempt, User $student): ExamSessionAttempt
    {
        $attempt = ExamSessionAttempt::query()->lockForUpdate()->with(['session.version.questions', 'answers'])->findOrFail($attempt->id);
        if ((int) $attempt->user_id !== (int) $student->getAuthIdentifier()) {
            throw new AuthorizationException;
        }

        return $attempt;
    }

    private function keepAlive(ExamSessionAttempt $attempt): bool
    {
        if ($attempt->status !== ExamSessionAttempt::STATUS_IN_PROGRESS) {
            return false;
        }
        if ($attempt->expires_at->isPast()) {
            $this->finalize($attempt, ExamSessionAttempt::STATUS_EXPIRED);

            return false;
        }

        $attempt->update(['last_activity_at' => now()]);

        return true;
    }

    private function finalize(ExamSessionAttempt $attempt, string $status): void
    {
        [$score, $maxScore, $needsReview] = $this->grade($attempt);
        $attempt->update([
            'status' => $status,
            'submitted_at' => now(),
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => $maxScore > 0 ? round($score / $maxScore * 100, 2) : 0,
            'passed' => $needsReview ? null : $score >= (float) $attempt->session->passing_score,
            'needs_review' => $needsReview,
            'grading_status' => $needsReview ? ExamSessionAttempt::GRADING_PENDING_MANUAL : ExamSessionAttempt::GRADING_COMPLETED,
        ]);
    }

    private function normalizeAnswer(mixed $answer): array
    {
        return collect(is_array($answer) ? $answer : [$answer])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (string) $value)->values()->all();
    }

    private function isOpen(ExamSession $session): bool
    {
        return in_array($session->status, [ExamSession::STATUS_SCHEDULED, ExamSession::STATUS_LIVE], true)
            && (! $session->starts_at || $session->starts_at->isPast())
            && (! $session->ends_at || $session->ends_at->isFuture());
    }

    private function answerOrder(Collection $questions, bool $shuffle): array
    {
        return $questions->mapWithKeys(function (ExamTemplateQuestion $question) use ($shuffle): array {
            $keys = collect($question->options ?? [])->map(fn ($option, $key) => is_array($option) ? ($option['key'] ?? $key) : $key)->values()->all();
            if ($shuffle) {
                shuffle($keys);
            }

            return [$question->id => $keys];
        })->all();
    }
}
