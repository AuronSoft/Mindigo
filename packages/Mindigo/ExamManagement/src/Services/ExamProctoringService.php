<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Events\ExamMonitoringUpdated;
use Mindigo\ExamManagement\Models\ExamProctorEvent;
use Mindigo\ExamManagement\Models\ExamSessionAttempt;

class ExamProctoringService
{
    private const WEIGHTS = [
        ExamProctorEvent::TYPE_TAB_HIDDEN => 5,
        ExamProctorEvent::TYPE_FULLSCREEN_EXITED => 10,
        ExamProctorEvent::TYPE_COPY_ATTEMPT => 5,
        ExamProctorEvent::TYPE_PASTE_ATTEMPT => 5,
        ExamProctorEvent::TYPE_ABNORMAL_REFRESH => 5,
        ExamProctorEvent::TYPE_HEARTBEAT_MISSED => 10,
        ExamProctorEvent::TYPE_CONNECTION_LOST => 15,
        ExamProctorEvent::TYPE_IP_CHANGED => 15,
        ExamProctorEvent::TYPE_DEVICE_CHANGED => 15,
        ExamProctorEvent::TYPE_CONCURRENT_SESSION => 30,
    ];

    public function heartbeat(ExamSessionAttempt $attempt, User $student, array $context): bool
    {
        return DB::transaction(function () use ($attempt, $student, $context): bool {
            $attempt = $this->ownedActiveAttempt($attempt, $student);
            if (! $attempt) {
                return false;
            }

            if ($attempt->status === ExamSessionAttempt::STATUS_PAUSED) {
                return true;
            }

            $policy = $attempt->session->security_policy ?? [];
            $lastActivity = $attempt->last_activity_at;
            $gap = $lastActivity?->diffInSeconds(now()) ?? 0;

            if (data_get($policy, 'heartbeat_detection', true)) {
                $grace = (int) data_get($policy, 'heartbeat_grace_seconds', 90);
                $disconnect = (int) data_get($policy, 'disconnect_threshold_seconds', 180);
                if ($gap >= $disconnect) {
                    $this->record($attempt, ExamProctorEvent::TYPE_CONNECTION_LOST, ExamProctorEvent::SOURCE_SERVER, $student, $context, ['seconds' => $gap], true);
                } elseif ($gap >= $grace) {
                    $this->record($attempt, ExamProctorEvent::TYPE_HEARTBEAT_MISSED, ExamProctorEvent::SOURCE_SERVER, $student, $context, ['seconds' => $gap], true);
                }
            }

            $this->inspectContext($attempt, $student, $context, $policy);
            $currentQuestionId = $context['current_question_id'] ?? null;
            $attempt->update([
                'last_activity_at' => now(),
                'current_question_id' => $currentQuestionId && in_array($currentQuestionId, $attempt->question_order ?? [], true)
                    ? $currentQuestionId
                    : $attempt->current_question_id,
            ]);
            $this->broadcast($attempt, 'heartbeat');

            return true;
        });
    }

    public function recordClientEvent(ExamSessionAttempt $attempt, User $student, string $type, array $context, array $metadata = []): ExamProctorEvent
    {
        return DB::transaction(function () use ($attempt, $student, $type, $context, $metadata): ExamProctorEvent {
            $attempt = $this->requireOwnedActiveAttempt($attempt, $student);
            if (! $this->eventEnabled($attempt, $type)) {
                throw ValidationException::withMessages(['type' => __('Mindigo-exam-management::app.proctoring.event_disabled')]);
            }
            $this->inspectContext($attempt, $student, $context, $attempt->session->security_policy ?? []);

            $event = $this->record($attempt, $type, ExamProctorEvent::SOURCE_CLIENT, $student, $context, $metadata);
            $legacyEvents = collect($attempt->security_events ?? [])->push([
                'type' => $type,
                'occurred_at' => $event->occurred_at->toIso8601String(),
            ])->take(-500)->values()->all();
            $attempt->update(['security_events' => $legacyEvents]);

            return $event;
        });
    }

    public function recordCameraConsent(ExamSessionAttempt $attempt, User $student, bool $consented, array $context): ExamProctorEvent
    {
        return DB::transaction(function () use ($attempt, $student, $consented, $context): ExamProctorEvent {
            $attempt = $this->requireOwnedActiveAttempt($attempt, $student);
            if (! data_get($attempt->session->security_policy, 'camera_enabled', false)) {
                throw ValidationException::withMessages(['camera' => __('Mindigo-exam-management::app.proctoring.camera_disabled')]);
            }

            $attempt->update($consented
                ? ['camera_consent_at' => now(), 'camera_consent_declined_at' => null]
                : ['camera_consent_at' => null, 'camera_consent_declined_at' => now()]);

            return $this->record(
                $attempt,
                $consented ? ExamProctorEvent::TYPE_CAMERA_CONSENT_GRANTED : ExamProctorEvent::TYPE_CAMERA_CONSENT_DENIED,
                ExamProctorEvent::SOURCE_CLIENT,
                $student,
                $context,
            );
        });
    }

    public function storeSnapshot(ExamSessionAttempt $attempt, User $student, UploadedFile $snapshot, array $context): ExamProctorEvent
    {
        $attempt = $this->requireOwnedActiveAttempt($attempt, $student);
        if (! data_get($attempt->session->security_policy, 'camera_enabled', false) || ! $attempt->camera_consent_at) {
            throw ValidationException::withMessages(['snapshot' => __('Mindigo-exam-management::app.proctoring.camera_consent_required')]);
        }

        $path = $snapshot->store("exam-proctoring/{$attempt->id}", ['disk' => 'local']);

        return DB::transaction(fn (): ExamProctorEvent => $this->record(
            $attempt->fresh('session'), ExamProctorEvent::TYPE_CAMERA_SNAPSHOT,
            ExamProctorEvent::SOURCE_CLIENT, $student, $context, [], false, $path,
        ));
    }

    public function addNote(ExamSessionAttempt $attempt, User $teacher, string $note): ExamProctorEvent
    {
        $this->authorizeOrganizer($attempt, $teacher);

        return DB::transaction(fn (): ExamProctorEvent => $this->record(
            $attempt->fresh('session'), ExamProctorEvent::TYPE_PROCTOR_NOTE,
            ExamProctorEvent::SOURCE_PROCTOR, $teacher, [], ['note' => $note],
        ));
    }

    public function addTime(ExamSessionAttempt $attempt, User $teacher, int $minutes): ExamSessionAttempt
    {
        $this->authorizeOrganizer($attempt, $teacher);

        return DB::transaction(function () use ($attempt, $teacher, $minutes): ExamSessionAttempt {
            $attempt = ExamSessionAttempt::query()->lockForUpdate()->with('session')->findOrFail($attempt->id);
            if (! in_array($attempt->status, [ExamSessionAttempt::STATUS_IN_PROGRESS, ExamSessionAttempt::STATUS_PAUSED], true)) {
                throw ValidationException::withMessages(['attempt' => __('Mindigo-exam-management::app.proctoring.attempt_not_active')]);
            }

            $attempt->update([
                'expires_at' => $attempt->expires_at->addMinutes($minutes),
                'pause_remaining_seconds' => $attempt->pause_remaining_seconds === null ? null : $attempt->pause_remaining_seconds + ($minutes * 60),
                'added_time_minutes' => $attempt->added_time_minutes + $minutes,
            ]);
            $this->record($attempt, ExamProctorEvent::TYPE_TIME_ADDED, ExamProctorEvent::SOURCE_PROCTOR, $teacher, [], ['minutes' => $minutes]);

            return $attempt->fresh();
        });
    }

    public function allowRetry(ExamSessionAttempt $attempt, User $teacher): void
    {
        $this->authorizeOrganizer($attempt, $teacher);

        DB::transaction(function () use ($attempt, $teacher): void {
            $attempt = ExamSessionAttempt::query()->lockForUpdate()->with(['session', 'candidate'])->findOrFail($attempt->id);
            if (! in_array($attempt->status, [ExamSessionAttempt::STATUS_SUBMITTED, ExamSessionAttempt::STATUS_EXPIRED, ExamSessionAttempt::STATUS_TERMINATED], true)) {
                throw ValidationException::withMessages(['attempt' => __('Mindigo-exam-management::app.proctoring.retry_not_available')]);
            }
            $used = $attempt->candidate->attempts()->count();
            $currentLimit = $attempt->candidate->max_attempts_override ?? $attempt->session->max_attempts;
            $attempt->candidate->update(['max_attempts_override' => max($used + 1, $currentLimit)]);
            $this->record($attempt, ExamProctorEvent::TYPE_RETRY_ALLOWED, ExamProctorEvent::SOURCE_PROCTOR, $teacher, [], ['max_attempts' => max($used + 1, $currentLimit)]);
        });
    }

    public function sendWarning(ExamSessionAttempt $attempt, User $teacher, string $message): ExamSessionAttempt
    {
        $this->authorizeOrganizer($attempt, $teacher);

        return DB::transaction(function () use ($attempt, $teacher, $message): ExamSessionAttempt {
            $attempt = ExamSessionAttempt::query()->lockForUpdate()->with('session')->findOrFail($attempt->id);
            if (! in_array($attempt->status, [ExamSessionAttempt::STATUS_IN_PROGRESS, ExamSessionAttempt::STATUS_PAUSED], true)) {
                throw ValidationException::withMessages(['attempt' => __('Mindigo-exam-management::app.proctoring.attempt_not_active')]);
            }
            $attempt->update(['latest_warning' => $message, 'latest_warning_at' => now()]);
            $this->record($attempt, ExamProctorEvent::TYPE_WARNING_SENT, ExamProctorEvent::SOURCE_PROCTOR, $teacher, [], ['message' => $message]);

            return $attempt->fresh();
        });
    }

    public function pause(ExamSessionAttempt $attempt, User $teacher): ExamSessionAttempt
    {
        $this->authorizeOrganizer($attempt, $teacher);

        return DB::transaction(function () use ($attempt, $teacher): ExamSessionAttempt {
            $attempt = ExamSessionAttempt::query()->lockForUpdate()->with('session')->findOrFail($attempt->id);
            if ($attempt->status !== ExamSessionAttempt::STATUS_IN_PROGRESS) {
                throw ValidationException::withMessages(['attempt' => __('Mindigo-exam-management::app.proctoring.attempt_not_active')]);
            }
            $attempt->update([
                'status' => ExamSessionAttempt::STATUS_PAUSED,
                'paused_at' => now(),
                'paused_by' => $teacher->getAuthIdentifier(),
                'pause_remaining_seconds' => max(0, (int) now()->diffInSeconds($attempt->expires_at, false)),
            ]);
            $this->record($attempt, ExamProctorEvent::TYPE_ATTEMPT_PAUSED, ExamProctorEvent::SOURCE_PROCTOR, $teacher);

            return $attempt->fresh();
        });
    }

    public function resume(ExamSessionAttempt $attempt, User $teacher): ExamSessionAttempt
    {
        $this->authorizeOrganizer($attempt, $teacher);

        return DB::transaction(function () use ($attempt, $teacher): ExamSessionAttempt {
            $attempt = ExamSessionAttempt::query()->lockForUpdate()->with('session')->findOrFail($attempt->id);
            if ($attempt->status !== ExamSessionAttempt::STATUS_PAUSED) {
                throw ValidationException::withMessages(['attempt' => __('Mindigo-exam-management::app.proctoring.attempt_not_paused')]);
            }
            $attempt->update([
                'status' => ExamSessionAttempt::STATUS_IN_PROGRESS,
                'expires_at' => now()->addSeconds($attempt->pause_remaining_seconds ?? 0),
                'paused_at' => null,
                'paused_by' => null,
                'pause_remaining_seconds' => null,
                'last_activity_at' => now(),
            ]);
            $this->record($attempt, ExamProctorEvent::TYPE_ATTEMPT_RESUMED, ExamProctorEvent::SOURCE_PROCTOR, $teacher);

            return $attempt->fresh();
        });
    }

    public function terminate(ExamSessionAttempt $attempt, User $teacher, string $reason): ExamSessionAttempt
    {
        $this->authorizeOrganizer($attempt, $teacher);

        return DB::transaction(function () use ($attempt, $teacher, $reason): ExamSessionAttempt {
            $attempt = ExamSessionAttempt::query()->lockForUpdate()->with('session')->findOrFail($attempt->id);
            if (! in_array($attempt->status, [ExamSessionAttempt::STATUS_IN_PROGRESS, ExamSessionAttempt::STATUS_PAUSED], true)) {
                throw ValidationException::withMessages(['attempt' => __('Mindigo-exam-management::app.proctoring.attempt_not_active')]);
            }

            $attempt->update([
                'status' => ExamSessionAttempt::STATUS_TERMINATED,
                'submitted_at' => now(),
                'terminated_by' => $teacher->getAuthIdentifier(),
                'terminated_at' => now(),
                'termination_reason' => $reason,
            ]);
            $this->record($attempt, ExamProctorEvent::TYPE_ATTEMPT_TERMINATED, ExamProctorEvent::SOURCE_PROCTOR, $teacher, [], ['reason' => $reason]);

            return $attempt->fresh();
        });
    }

    private function inspectContext(ExamSessionAttempt $attempt, User $student, array $context, array $policy): void
    {
        $sessionKey = $context['session_key'] ?? null;
        $ipHash = $this->hash($context['ip'] ?? null);
        $deviceHash = $this->hash($context['device_key'] ?? null);

        if ($attempt->proctor_session_key && $sessionKey && ! hash_equals($attempt->proctor_session_key, $sessionKey) && data_get($policy, 'multiple_sessions_detection', true)) {
            $this->record($attempt, ExamProctorEvent::TYPE_CONCURRENT_SESSION, ExamProctorEvent::SOURCE_SERVER, $student, $context, [], true);
        }
        if ($attempt->initial_ip_hash && $ipHash && ! hash_equals($attempt->initial_ip_hash, $ipHash) && data_get($policy, 'ip_change_detection', true)) {
            $this->record($attempt, ExamProctorEvent::TYPE_IP_CHANGED, ExamProctorEvent::SOURCE_SERVER, $student, $context, [], true);
        }
        if ($attempt->initial_device_hash && $deviceHash && ! hash_equals($attempt->initial_device_hash, $deviceHash) && data_get($policy, 'device_change_detection', true)) {
            $this->record($attempt, ExamProctorEvent::TYPE_DEVICE_CHANGED, ExamProctorEvent::SOURCE_SERVER, $student, $context, [], true);
        }

        $attempt->update([
            'proctor_session_key' => $attempt->proctor_session_key ?? $sessionKey,
            'initial_ip_hash' => $attempt->initial_ip_hash ?? $ipHash,
            'last_ip_hash' => $ipHash ?? $attempt->last_ip_hash,
            'initial_device_hash' => $attempt->initial_device_hash ?? $deviceHash,
            'last_device_hash' => $deviceHash ?? $attempt->last_device_hash,
        ]);
    }

    private function record(ExamSessionAttempt $attempt, string $type, string $source, ?User $actor, array $context = [], array $metadata = [], bool $deduplicate = false, ?string $evidencePath = null): ExamProctorEvent
    {
        if ($deduplicate) {
            $existing = $attempt->proctorEvents()->where('type', $type)->where('occurred_at', '>=', now()->subMinute())->latest()->first();
            if ($existing) {
                return $existing;
            }
        }

        $weight = self::WEIGHTS[$type] ?? 0;
        $score = min(100, (int) $attempt->risk_score + $weight);
        $level = $score >= 50 ? ExamProctorEvent::RISK_HIGH : ($score >= 20 ? ExamProctorEvent::RISK_MEDIUM : ExamProctorEvent::RISK_LOW);
        $event = $attempt->proctorEvents()->create([
            'exam_session_id' => $attempt->exam_session_id,
            'actor_id' => $actor?->getAuthIdentifier(),
            'type' => $type,
            'source' => $source,
            'risk_level' => $level,
            'risk_weight' => $weight,
            'session_key' => $context['session_key'] ?? null,
            'ip_hash' => $this->hash($context['ip'] ?? null),
            'device_hash' => $this->hash($context['device_key'] ?? null),
            'evidence_path' => $evidencePath,
            'metadata' => $metadata ?: null,
            'occurred_at' => $context['occurred_at'] ?? now(),
        ]);

        if ($weight > 0) {
            $attempt->update(['risk_score' => $score, 'risk_level' => $level]);
        }

        $this->broadcast($attempt, $type);

        return $event;
    }

    private function ownedActiveAttempt(ExamSessionAttempt $attempt, User $student): ?ExamSessionAttempt
    {
        $attempt = ExamSessionAttempt::query()->lockForUpdate()->with('session')->findOrFail($attempt->id);
        if ((int) $attempt->user_id !== (int) $student->getAuthIdentifier()) {
            throw new AuthorizationException;
        }
        if ($attempt->status === ExamSessionAttempt::STATUS_IN_PROGRESS && $attempt->expires_at->isPast()) {
            $attempt->update(['status' => ExamSessionAttempt::STATUS_EXPIRED, 'submitted_at' => now()]);

            return null;
        }

        return $attempt->isActive() || $attempt->status === ExamSessionAttempt::STATUS_PAUSED ? $attempt : null;
    }

    private function requireOwnedActiveAttempt(ExamSessionAttempt $attempt, User $student): ExamSessionAttempt
    {
        return $this->ownedActiveAttempt($attempt, $student)
            ?? throw ValidationException::withMessages(['attempt' => __('Mindigo-exam-management::app.proctoring.attempt_not_active')]);
    }

    private function authorizeOrganizer(ExamSessionAttempt $attempt, User $teacher): void
    {
        $attempt->loadMissing('session');
        if ((int) $attempt->session->organizer_id !== (int) $teacher->getAuthIdentifier()) {
            throw new AuthorizationException;
        }
    }

    private function hash(?string $value): ?string
    {
        return $value ? hash_hmac('sha256', $value, (string) config('app.key')) : null;
    }

    private function eventEnabled(ExamSessionAttempt $attempt, string $type): bool
    {
        $setting = match ($type) {
            ExamProctorEvent::TYPE_TAB_HIDDEN => 'tab_switch_detection',
            ExamProctorEvent::TYPE_FULLSCREEN_EXITED => 'fullscreen',
            ExamProctorEvent::TYPE_COPY_ATTEMPT, ExamProctorEvent::TYPE_PASTE_ATTEMPT => 'clipboard_detection',
            ExamProctorEvent::TYPE_ABNORMAL_REFRESH => 'refresh_detection',
            default => null,
        };

        return $setting === null || (bool) data_get($attempt->session->security_policy, $setting, true);
    }

    private function broadcast(ExamSessionAttempt $attempt, string $reason): void
    {
        DB::afterCommit(fn () => ExamMonitoringUpdated::dispatch($attempt->exam_session_id, $attempt->id, $reason));
    }
}
