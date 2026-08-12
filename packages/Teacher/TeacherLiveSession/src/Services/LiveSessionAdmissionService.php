<?php

namespace Mindigo\TeacherLiveSession\Services;

use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionParticipant;

final class LiveSessionAdmissionService
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly LiveSessionConfigurationService $configuration,
    ) {}

    public function requestEntry(LiveSession $session, User $user, LiveParticipantRole $role): LiveSessionParticipant
    {
        $autoAdmit = $role->canModerate() || ! (bool) data_get($session->room_settings, 'waiting_room_enabled', true);
        if ($autoAdmit && ! $session->participants()->where('user_id', $user->id)->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->exists()) {
            $this->assertCapacity($session);
        }
        $participant = LiveSessionParticipant::query()->firstOrCreate(
            ['live_session_id' => $session->id, 'user_id' => $user->id],
            [
                'role' => $role,
                'admission_status' => $autoAdmit ? ParticipantAdmissionStatus::Admitted : ParticipantAdmissionStatus::Waiting,
                'admitted_at' => $autoAdmit ? now() : null,
            ],
        );

        if ($autoAdmit && $participant->admission_status === ParticipantAdmissionStatus::Waiting) {
            $participant->update(['admission_status' => ParticipantAdmissionStatus::Admitted, 'admitted_at' => now()]);
        }
        $participant->update(['role' => $role, 'last_seen_at' => now()]);

        return $participant->fresh('user');
    }

    public function admit(LiveSession $session, LiveSessionParticipant $participant, User $actor): LiveSessionParticipant
    {
        $this->ensureBelongsToSession($session, $participant);
        $this->assertCapacity($session);
        $participant->update([
            'admission_status' => ParticipantAdmissionStatus::Admitted,
            'admitted_by' => $actor->id,
            'admitted_at' => now(),
            'denied_at' => null,
            'removed_at' => null,
        ]);
        $this->auditDecision('participant_admitted', $session, $participant, $actor);

        return $participant->fresh();
    }

    public function deny(LiveSession $session, LiveSessionParticipant $participant, User $actor): LiveSessionParticipant
    {
        $this->ensureBelongsToSession($session, $participant);
        abort_if($participant->role->canModerate(), 422);
        $participant->update(['admission_status' => ParticipantAdmissionStatus::Denied, 'denied_at' => now()]);
        $this->auditDecision('participant_denied', $session, $participant, $actor);

        return $participant->fresh();
    }

    public function remove(LiveSession $session, LiveSessionParticipant $participant, User $actor): LiveSessionParticipant
    {
        $this->ensureBelongsToSession($session, $participant);
        abort_if($participant->role->canModerate(), 422);
        $participant->update(['admission_status' => ParticipantAdmissionStatus::Removed, 'removed_at' => now()]);
        $this->auditDecision('participant_removed', $session, $participant, $actor);

        return $participant->fresh();
    }

    private function ensureBelongsToSession(LiveSession $session, LiveSessionParticipant $participant): void
    {
        abort_unless((int) $participant->live_session_id === (int) $session->id, 404);
    }

    private function auditDecision(string $action, LiveSession $session, LiveSessionParticipant $participant, User $actor): void
    {
        $this->audit->record($action, 'teacher_live_session', metadata: [
            'participant_id' => $participant->id,
            'participant_user_id' => $participant->user_id,
        ], auditable: $session, user: $actor);
    }

    private function assertCapacity(LiveSession $session): void
    {
        $admitted = $session->participants()->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->count()
            + $session->guests()->where('admission_status', ParticipantAdmissionStatus::Admitted->value)->count();
        abort_if($admitted >= (int) $this->configuration->value('live_max_participants'), 422, __('teacher-live-session::app.validation.room_capacity_reached'));
    }
}
