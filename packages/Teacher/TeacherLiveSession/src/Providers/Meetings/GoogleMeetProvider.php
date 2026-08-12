<?php

namespace Mindigo\TeacherLiveSession\Providers\Meetings;

use Illuminate\Support\Facades\Http;
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
use Mindigo\TeacherLiveSession\Services\LiveProviderTokenService;

final class GoogleMeetProvider implements LiveMeetingProvider
{
    private const API = 'https://www.googleapis.com/calendar/v3';

    public function __construct(private readonly LiveProviderTokenService $tokens) {}

    public function key(): LiveSessionProvider
    {
        return LiveSessionProvider::GoogleMeet;
    }

    public function create(SessionContext $context): ProviderMeeting
    {
        $response = Http::withToken($this->tokens->accessToken($context->teacherId, $this->key()))->acceptJson()->post(self::API.'/calendars/primary/events?conferenceDataVersion=1', [
            'summary' => $context->title, 'description' => $context->description,
            'start' => ['dateTime' => $context->scheduledStart->toRfc3339String()],
            'end' => ['dateTime' => ($context->scheduledEnd ?? $context->scheduledStart->addHour())->toRfc3339String()],
            'conferenceData' => ['createRequest' => ['requestId' => $context->idempotencyKey, 'conferenceSolutionKey' => ['type' => 'hangoutsMeet']]],
        ])->throw()->json();
        $url = $response['hangoutLink'] ?? collect($response['conferenceData']['entryPoints'] ?? [])->firstWhere('entryPointType', 'video')['uri'] ?? null;

        return new ProviderMeeting('google-'.$response['id'], (string) $response['id'], $url, $url, $response['status'] ?? 'confirmed', ['html_link' => $response['htmlLink'] ?? null]);
    }

    public function update(LiveSession $session): ProviderMeeting
    {
        $response = Http::withToken($this->tokens->accessToken((int) $session->teacher_id, $this->key()))
            ->acceptJson()
            ->patch(self::API.'/calendars/primary/events/'.rawurlencode((string) $session->provider_meeting_id), [
                'summary' => $session->title,
                'description' => $session->description,
                'start' => ['dateTime' => $session->scheduled_start->toRfc3339String()],
                'end' => ['dateTime' => $session->scheduled_end->toRfc3339String()],
            ])->throw()->json();

        $session->provider_status = $response['status'] ?? $session->provider_status;

        return $this->fromSession($session);
    }

    public function start(LiveSession $session, User $actor): JoinResult
    {
        return new JoinResult('redirect', $session->provider_host_url);
    }

    public function join(LiveSession $session, User $actor): JoinResult
    {
        return new JoinResult('redirect', $session->provider_join_url);
    }

    public function end(LiveSession $session, User $actor): void {}

    public function sync(LiveSession $session): SyncResult
    {
        return new SyncResult('synced', $session->provider_metadata ?? []);
    }

    public function capabilities(): ProviderCapabilities
    {
        return new ProviderCapabilities(false, true, false, false);
    }

    public function health(): ProviderHealth
    {
        return new ProviderHealth(filled(config('live-providers.google_meet.client_id')));
    }

    private function fromSession(LiveSession $s): ProviderMeeting
    {
        return new ProviderMeeting($s->room_name, $s->provider_meeting_id, $s->provider_join_url, $s->provider_host_url, $s->provider_status, $s->provider_metadata ?? []);
    }
}
