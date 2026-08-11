<?php

namespace Mindigo\TeacherLiveSession\Providers\Meetings;

use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Contracts\LiveMeetingProvider;
use Mindigo\TeacherLiveSession\Data\JoinResult;
use Mindigo\TeacherLiveSession\Data\ProviderCapabilities;
use Mindigo\TeacherLiveSession\Data\ProviderHealth;
use Mindigo\TeacherLiveSession\Data\ProviderMeeting;
use Mindigo\TeacherLiveSession\Data\SessionContext;
use Mindigo\TeacherLiveSession\Data\SyncResult;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Models\LiveSession;

final class MindigoNativeProvider implements LiveMeetingProvider
{
    public function key(): LiveSessionProvider
    {
        return LiveSessionProvider::Native;
    }

    public function create(SessionContext $context): ProviderMeeting
    {
        return new ProviderMeeting(
            roomName: 'mindigo-'.Str::lower(Str::random(24)),
            status: 'ready',
            metadata: ['architecture' => 'mindigo_native'],
        );
    }

    public function update(LiveSession $session): ProviderMeeting
    {
        return $this->fromSession($session);
    }

    public function start(LiveSession $session, User $actor): JoinResult
    {
        return $this->join($session, $actor);
    }

    public function join(LiveSession $session, User $actor): JoinResult
    {
        return new JoinResult('embedded', metadata: [
            'room_name' => $session->room_name,
            'participant_id' => (string) $actor->getKey(),
        ]);
    }

    public function end(LiveSession $session, User $actor): void {}

    public function sync(LiveSession $session): SyncResult
    {
        return new SyncResult('not_required', $session->provider_metadata ?? []);
    }

    public function capabilities(): ProviderCapabilities
    {
        return new ProviderCapabilities(
            embedded: true,
            guestLinks: true,
            attendanceSync: true,
            recording: false,
        );
    }

    public function health(): ProviderHealth
    {
        return new ProviderHealth(true);
    }

    private function fromSession(LiveSession $session): ProviderMeeting
    {
        return new ProviderMeeting(
            roomName: $session->room_name,
            meetingId: $session->provider_meeting_id,
            joinUrl: $session->provider_join_url,
            hostUrl: $session->provider_host_url,
            status: $session->provider_status,
            metadata: $session->provider_metadata ?? [],
        );
    }
}
