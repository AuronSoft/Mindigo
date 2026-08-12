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

final class ZoomProvider implements LiveMeetingProvider
{
    private const API = 'https://api.zoom.us/v2';

    public function __construct(private readonly LiveProviderTokenService $tokens) {}

    public function key(): LiveSessionProvider
    {
        return LiveSessionProvider::Zoom;
    }

    public function create(SessionContext $context): ProviderMeeting
    {
        $end = $context->scheduledEnd ?? $context->scheduledStart->addHour();
        $response = $this->client($context->teacherId)->post(self::API.'/users/me/meetings', [
            'topic' => $context->title, 'agenda' => $context->description, 'type' => 2,
            'start_time' => $context->scheduledStart->toRfc3339String(),
            'duration' => max(1, (int) ceil($context->scheduledStart->diffInMinutes($end))),
            'timezone' => config('app.timezone'), 'settings' => ['waiting_room' => true, 'join_before_host' => false],
        ])->throw()->json();

        return new ProviderMeeting('zoom-'.$response['id'], (string) $response['id'], $response['join_url'] ?? null, $response['start_url'] ?? null, $response['status'] ?? 'waiting', ['uuid' => $response['uuid'] ?? null]);
    }

    public function update(LiveSession $session): ProviderMeeting
    {
        $duration = max(1, (int) ceil($session->scheduled_start->diffInMinutes($session->scheduled_end)));
        $this->client((int) $session->teacher_id)
            ->patch(self::API.'/meetings/'.rawurlencode((string) $session->provider_meeting_id), [
                'topic' => $session->title,
                'agenda' => $session->description,
                'start_time' => $session->scheduled_start->toRfc3339String(),
                'duration' => $duration,
                'timezone' => config('app.timezone'),
            ])->throw();

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

    public function end(LiveSession $session, User $actor): void
    {
        $this->client((int) $session->teacher_id)->put(self::API.'/meetings/'.$session->provider_meeting_id.'/status', ['action' => 'end'])->throw();
    }

    public function sync(LiveSession $session): SyncResult
    {
        $meeting = $this->client((int) $session->teacher_id)->get(self::API.'/meetings/'.rawurlencode((string) $session->provider_meeting_id))
            ->throw()->json();

        return new SyncResult($meeting['status'] ?? 'waiting', [
            ...($session->provider_metadata ?? []),
            'uuid' => $meeting['uuid'] ?? ($session->provider_metadata['uuid'] ?? null),
            'external_updated_at' => $meeting['start_time'] ?? null,
        ]);
    }

    public function capabilities(): ProviderCapabilities
    {
        return new ProviderCapabilities(false, true, false, false);
    }

    public function health(): ProviderHealth
    {
        return new ProviderHealth(filled(config('live-providers.zoom.client_id')));
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
