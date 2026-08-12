<?php

namespace Mindigo\TeacherLiveSession\Providers\Meetings;

use Illuminate\Http\Client\PendingRequest;
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
        $response = $this->client($context->teacherId)->post(self::API.'/calendars/primary/events?conferenceDataVersion=1', [
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
        $response = $this->client((int) $session->teacher_id)
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
        $event = $this->client((int) $session->teacher_id)->get(self::API.'/calendars/primary/events/'.rawurlencode((string) $session->provider_meeting_id))
            ->throw()->json();

        return new SyncResult($event['status'] ?? 'confirmed', [
            ...($session->provider_metadata ?? []),
            'html_link' => $event['htmlLink'] ?? ($session->provider_metadata['html_link'] ?? null),
            'external_updated_at' => $event['updated'] ?? null,
        ]);
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

    private function client(int $userId): PendingRequest
    {
        return Http::withToken($this->tokens->accessToken($userId, $this->key()))->acceptJson()
            ->connectTimeout(config('live-providers.http.connect_timeout', 3))
            ->timeout(config('live-providers.http.timeout', 10))
            ->retry(config('live-providers.http.retries', 2), fn (int $attempt): int => 200 * (2 ** ($attempt - 1)));
    }
}
