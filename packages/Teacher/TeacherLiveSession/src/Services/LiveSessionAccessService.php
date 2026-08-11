<?php

namespace Mindigo\TeacherLiveSession\Services;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Enums\LiveSessionStatus;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;

final class LiveSessionAccessService
{
    public function roleFor(LiveSession $session, User $user): ?LiveParticipantRole
    {
        if ($user->role === 'admin' || (int) $session->teacher_id === (int) $user->getKey()) {
            return LiveParticipantRole::Host;
        }

        $session->loadMissing('classroom');
        if ((int) $session->classroom?->assistant_id === (int) $user->getKey()) {
            return LiveParticipantRole::CoHost;
        }

        $isAcceptedSubstitute = $session->schedule()
            ->where('substitute_teacher_id', $user->getKey())
            ->where('substitute_status', ClassroomSchedule::SUBSTITUTE_ACCEPTED)
            ->exists();
        if ($isAcceptedSubstitute) {
            return LiveParticipantRole::CoHost;
        }

        $isStudent = $session->classroom->students()
            ->whereKey($user->getKey())
            ->wherePivot('status', 'active')
            ->exists();

        return $isStudent ? LiveParticipantRole::Student : null;
    }

    public function canManage(LiveSession $session, User $user): bool
    {
        return $this->roleFor($session, $user) === LiveParticipantRole::Host;
    }

    public function canModerate(LiveSession $session, User $user): bool
    {
        return $this->roleFor($session, $user)?->canModerate() ?? false;
    }

    public function canEnter(LiveSession $session, User $user): bool
    {
        $role = $this->roleFor($session, $user);
        if (! $role || LiveSessionStatus::tryFrom($session->status)?->isTerminal()) {
            return false;
        }

        if ($role->canModerate()) {
            return in_array($session->status, [LiveSessionStatus::Scheduled->value, LiveSessionStatus::Waiting->value, LiveSessionStatus::Live->value], true)
                && now()->gte($session->scheduled_start->copy()->subHour())
                && (! $session->scheduled_end || now()->lte($session->scheduled_end->copy()->addMinutes(30)) || $session->isLive());
        }

        if (! in_array($session->status, [LiveSessionStatus::Waiting->value, LiveSessionStatus::Live->value], true)) {
            return false;
        }

        if (now()->lt($session->scheduled_start->copy()->subMinutes(15))) {
            return false;
        }

        if (! $session->isLive() && $session->scheduled_end && now()->gt($session->scheduled_end->copy()->addMinutes(30))) {
            return false;
        }

        if ($session->isLocked()) {
            return $session->participants()
                ->where('user_id', $user->getKey())
                ->where('admission_status', ParticipantAdmissionStatus::Admitted->value)
                ->exists();
        }

        return true;
    }
}
