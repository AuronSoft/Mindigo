<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;
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
        return DB::transaction(function () use ($session, $student): ExamSessionAttempt {
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
            ]);
        });
    }

    public function questions(ExamSessionAttempt $attempt, User $student): Collection
    {
        if ((int) $attempt->user_id !== (int) $student->getAuthIdentifier()) {
            throw new AuthorizationException;
        }

        $questions = ExamTemplateQuestion::query()->whereIn('id', $attempt->question_order)->get()->keyBy('id');

        return collect($attempt->question_order)->map(fn (int $id) => $questions->get($id))->filter()->values();
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
