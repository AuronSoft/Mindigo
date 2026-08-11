<?php

namespace Mindigo\TeacherLiveSession\Contracts;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Data\JoinResult;
use Mindigo\TeacherLiveSession\Data\ProviderCapabilities;
use Mindigo\TeacherLiveSession\Data\ProviderHealth;
use Mindigo\TeacherLiveSession\Data\ProviderMeeting;
use Mindigo\TeacherLiveSession\Data\SessionContext;
use Mindigo\TeacherLiveSession\Data\SyncResult;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Models\LiveSession;

interface LiveMeetingProvider
{
    public function key(): LiveSessionProvider;

    public function create(SessionContext $context): ProviderMeeting;

    public function update(LiveSession $session): ProviderMeeting;

    public function start(LiveSession $session, User $actor): JoinResult;

    public function join(LiveSession $session, User $actor): JoinResult;

    public function end(LiveSession $session, User $actor): void;

    public function sync(LiveSession $session): SyncResult;

    public function capabilities(): ProviderCapabilities;

    public function health(): ProviderHealth;
}
