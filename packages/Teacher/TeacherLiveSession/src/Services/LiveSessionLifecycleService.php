<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Enums\LiveSessionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;

final class LiveSessionLifecycleService
{
    public function __construct(
        private readonly LiveMeetingProviderRegistry $providers,
        private readonly AuditLogService $audit,
    ) {}

    public function openWaitingRoom(LiveSession $session, User $actor): LiveSession
    {
        return $this->transition($session, $actor, LiveSessionStatus::Waiting, [LiveSessionStatus::Scheduled]);
    }

    public function start(LiveSession $session, User $actor): LiveSession
    {
        $this->assertCurrentState($session, [LiveSessionStatus::Scheduled, LiveSessionStatus::Waiting]);
        $this->providers->resolve($session->provider)->start($session, $actor);

        return $this->transition($session, $actor, LiveSessionStatus::Live, [LiveSessionStatus::Scheduled, LiveSessionStatus::Waiting], [
            'started_at' => now(),
            'provider_status' => 'live',
        ]);
    }

    public function assertCanStart(LiveSession $session): void
    {
        $this->assertCurrentState($session, [LiveSessionStatus::Scheduled, LiveSessionStatus::Waiting]);
    }

    public function end(LiveSession $session, User $actor): LiveSession
    {
        $this->assertCurrentState($session, [LiveSessionStatus::Waiting, LiveSessionStatus::Live]);
        $this->providers->resolve($session->provider)->end($session, $actor);

        return $this->transition($session, $actor, LiveSessionStatus::Ended, [LiveSessionStatus::Waiting, LiveSessionStatus::Live], [
            'ended_at' => now(),
            'ended_by' => $actor->getKey(),
            'provider_status' => 'ended',
            'locked_at' => now(),
            'join_token_version' => DB::raw('join_token_version + 1'),
        ]);
    }

    public function cancel(LiveSession $session, User $actor, string $reason): LiveSession
    {
        return $this->transition($session, $actor, LiveSessionStatus::Cancelled, [LiveSessionStatus::Draft, LiveSessionStatus::Scheduled, LiveSessionStatus::Waiting], [
            'cancelled_at' => now(),
            'cancelled_by' => $actor->getKey(),
            'cancel_reason' => $reason,
            'locked_at' => now(),
            'join_token_version' => DB::raw('join_token_version + 1'),
        ]);
    }

    public function setLocked(LiveSession $session, User $actor, bool $locked): LiveSession
    {
        abort_unless(in_array($session->status, [LiveSessionStatus::Waiting->value, LiveSessionStatus::Live->value], true), 422);
        $session->update([
            'locked_at' => $locked ? now() : null,
            'join_token_version' => DB::raw('join_token_version + 1'),
        ]);
        $this->audit->record($locked ? 'room_locked' : 'room_unlocked', 'teacher_live_session', metadata: ['status' => $session->status], auditable: $session, user: $actor);

        return $session->fresh();
    }

    private function transition(LiveSession $session, User $actor, LiveSessionStatus $target, array $allowedFrom, array $attributes = []): LiveSession
    {
        $oldStatus = $session->status;
        $updated = DB::transaction(function () use ($session, $target, $allowedFrom, $attributes): LiveSession {
            $locked = LiveSession::query()->lockForUpdate()->findOrFail($session->id);
            if (! in_array($locked->status, array_map(fn (LiveSessionStatus $status) => $status->value, $allowedFrom), true)) {
                throw ValidationException::withMessages(['status' => __('teacher-live-session::app.validation.invalid_transition')]);
            }
            $locked->update(['status' => $target->value, ...$attributes]);

            return $locked->fresh();
        });

        $this->audit->record('status_changed', 'teacher_live_session', ['status' => $oldStatus], ['status' => $target->value], auditable: $updated, user: $actor);

        return $updated;
    }

    private function assertCurrentState(LiveSession $session, array $allowed): void
    {
        if (! in_array($session->status, array_map(fn (LiveSessionStatus $status) => $status->value, $allowed), true)) {
            throw ValidationException::withMessages(['status' => __('teacher-live-session::app.validation.invalid_transition')]);
        }
    }
}
