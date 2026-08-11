<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Enums\ParticipantAdmissionStatus;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Models\LiveSessionGuest;
use Mindigo\TeacherLiveSession\Models\LiveSessionGuestLink;

final class LiveSessionGuestService
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function createLink(LiveSession $session, User $actor, int $ttlMinutes, ?int $maxUses): array
    {
        abort_if(($session->room_settings['guest_access_enabled'] ?? false) !== true, 422);
        abort_if($session->isEnded(), 422);
        $plainToken = Str::random(64);
        $link = LiveSessionGuestLink::query()->create([
            'live_session_id' => $session->id, 'created_by' => $actor->id,
            'token_hash' => hash('sha256', $plainToken), 'max_uses' => $maxUses,
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);
        $this->audit->record('guest_link_created', 'teacher_live_session', metadata: ['guest_link_id' => $link->id, 'expires_at' => $link->expires_at->toIso8601String(), 'max_uses' => $maxUses], auditable: $session, user: $actor);

        return ['link' => $link, 'url' => route('live-guest.show', $plainToken)];
    }

    public function resolveLink(string $plainToken): LiveSessionGuestLink
    {
        $link = LiveSessionGuestLink::query()->with('session.classroom')->where('token_hash', hash('sha256', $plainToken))->firstOrFail();
        abort_unless($link->isUsable() && ($link->session->room_settings['guest_access_enabled'] ?? false), 410);

        return $link;
    }

    public function revokeLink(LiveSessionGuestLink $link, User $actor): void
    {
        $link->update(['revoked_at' => now()]);
        $this->audit->record('guest_link_revoked', 'teacher_live_session', metadata: ['guest_link_id' => $link->id], auditable: $link->session, user: $actor);
    }

    public function register(LiveSessionGuestLink $link, string $name, ?string $email): array
    {
        $plainAccessToken = Str::random(64);
        $guest = DB::transaction(function () use ($link, $name, $email, $plainAccessToken): LiveSessionGuest {
            $locked = LiveSessionGuestLink::query()->lockForUpdate()->findOrFail($link->id);
            if (! $locked->isUsable()) {
                throw ValidationException::withMessages(['link' => __('teacher-live-session::app.validation.guest_link_unavailable')]);
            }
            $guest = LiveSessionGuest::query()->create([
                'live_session_id' => $locked->live_session_id, 'guest_link_id' => $locked->id,
                'name' => trim($name), 'email' => filled($email) ? mb_strtolower(trim($email)) : null,
                'access_token_hash' => hash('sha256', $plainAccessToken),
                'admission_status' => ParticipantAdmissionStatus::Waiting,
            ]);
            $locked->increment('uses_count');

            return $guest;
        });

        return ['guest' => $guest, 'access_token' => $plainAccessToken];
    }

    public function resolveGuest(int $guestId, string $plainAccessToken): LiveSessionGuest
    {
        return LiveSessionGuest::query()->whereKey($guestId)
            ->where('access_token_hash', hash('sha256', $plainAccessToken))->with('session.classroom')->firstOrFail();
    }

    public function decide(LiveSessionGuest $guest, User $actor, ParticipantAdmissionStatus $status): void
    {
        abort_unless(in_array($status, [ParticipantAdmissionStatus::Admitted, ParticipantAdmissionStatus::Denied, ParticipantAdmissionStatus::Removed], true), 422);
        $guest->update([
            'admission_status' => $status, 'admitted_by' => $actor->id,
            'admitted_at' => $status === ParticipantAdmissionStatus::Admitted ? now() : $guest->admitted_at,
            'denied_at' => $status === ParticipantAdmissionStatus::Denied ? now() : null,
            'removed_at' => $status === ParticipantAdmissionStatus::Removed ? now() : null,
        ]);
        $this->audit->record('guest_'.$status->value, 'teacher_live_session', metadata: ['guest_id' => $guest->id], auditable: $guest->session, user: $actor);
    }
}
