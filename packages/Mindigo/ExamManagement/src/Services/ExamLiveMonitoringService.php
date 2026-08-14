<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;

class ExamLiveMonitoringService
{
    public function dashboard(ExamSession $session, User $teacher, ?string $status = null): array
    {
        $this->authorize($session, $teacher);
        $disconnectThreshold = (int) data_get($session->security_policy, 'disconnect_threshold_seconds', 180);
        $candidates = $session->candidates()->where('status', ExamCandidate::STATUS_ELIGIBLE)
            ->with(['user', 'attempts' => fn ($query) => $query->withCount(['proctorEvents as warning_count' => fn ($events) => $events->where('risk_weight', '>', 0)])->latest('attempt_number')])
            ->orderBy('name')->get()
            ->map(fn (ExamCandidate $candidate): array => $this->candidateRow($candidate, $disconnectThreshold));

        $summary = collect(['not_started', 'in_progress', 'disconnected', 'paused', 'submitted', 'terminated'])
            ->mapWithKeys(fn (string $state): array => [$state => $candidates->where('monitor_status', $state)->count()])->all();

        if ($status && in_array($status, array_keys($summary), true)) {
            $candidates = $candidates->where('monitor_status', $status)->values();
        }

        return ['session' => $session, 'candidates' => $candidates, 'summary' => $summary];
    }

    private function candidateRow(ExamCandidate $candidate, int $disconnectThreshold): array
    {
        /** @var ExamSessionAttempt|null $attempt */
        $attempt = $candidate->attempts->first();
        $status = $this->status($attempt, $disconnectThreshold);
        $remaining = $attempt?->status === ExamSessionAttempt::STATUS_PAUSED
            ? $attempt->pause_remaining_seconds
            : ($attempt ? max(0, (int) now()->diffInSeconds($attempt->expires_at, false)) : null);

        $currentQuestionIndex = $attempt?->current_question_id ? array_search($attempt->current_question_id, $attempt->question_order ?? [], true) : false;

        return [
            'candidate' => $candidate,
            'attempt' => $attempt,
            'monitor_status' => $status,
            'remaining_seconds' => $remaining,
            'last_activity_at' => $attempt?->last_activity_at,
            'current_question_number' => $currentQuestionIndex === false ? null : $currentQuestionIndex + 1,
            'warning_count' => (int) ($attempt?->warning_count ?? 0),
            'integrity_score' => $attempt ? max(0, 100 - $attempt->risk_score) : 100,
        ];
    }

    private function status(?ExamSessionAttempt $attempt, int $disconnectThreshold): string
    {
        if (! $attempt) {
            return 'not_started';
        }
        if ($attempt->status === ExamSessionAttempt::STATUS_IN_PROGRESS && $attempt->last_activity_at?->lt(now()->subSeconds($disconnectThreshold))) {
            return 'disconnected';
        }

        return match ($attempt->status) {
            ExamSessionAttempt::STATUS_IN_PROGRESS => 'in_progress',
            ExamSessionAttempt::STATUS_PAUSED => 'paused',
            ExamSessionAttempt::STATUS_TERMINATED => 'terminated',
            ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED => 'submitted',
            default => 'not_started',
        };
    }

    private function authorize(ExamSession $session, User $teacher): void
    {
        if (! $teacher->isTeacher() || (int) $session->organizer_id !== (int) $teacher->getAuthIdentifier()) {
            throw new AuthorizationException;
        }
    }
}
