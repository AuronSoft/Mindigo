<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Models\LiveProviderConnection;
use Mindigo\TeacherLiveSession\Models\LiveProviderSubscription;
use Throwable;

final class LiveProviderSubscriptionService
{
    public function __construct(private readonly LiveProviderTokenService $tokens, private readonly LiveProviderErrorSanitizer $errors) {}

    public function renewDue(): array
    {
        $stats = ['renewed' => 0, 'failed' => 0];
        LiveProviderConnection::query()->where('provider', 'google_meet')->whereNull('revoked_at')->get()
            ->each(function (LiveProviderConnection $connection) use (&$stats): void {
                $current = LiveProviderSubscription::query()->where('connection_id', $connection->id)->where('status', 'active')->latest()->first();
                if ($current?->expires_at?->isAfter(now()->addDay())) {
                    return;
                }
                try {
                    $this->watchGoogleCalendar($connection);
                    $stats['renewed']++;
                } catch (Throwable $exception) {
                    report($exception);
                    $current?->update(['status' => 'failed', 'last_error' => $this->errors->from($exception)]);
                    $stats['failed']++;
                }
            });

        return $stats;
    }

    public function watchGoogleCalendar(LiveProviderConnection $connection): LiveProviderSubscription
    {
        $channelId = (string) Str::uuid();
        $expiresAt = now()->addSeconds((int) config('live-providers.google_meet.watch_ttl_seconds', 604800));
        $response = Http::withToken($this->tokens->accessToken((int) $connection->user_id, LiveSessionProvider::GoogleMeet))
            ->acceptJson()->post('https://www.googleapis.com/calendar/v3/calendars/primary/events/watch', [
                'id' => $channelId,
                'type' => 'web_hook',
                'address' => config('live-providers.google_meet.calendar_webhook_url'),
                'token' => config('live-providers.google_meet.webhook_token'),
                'expiration' => (string) ($expiresAt->timestamp * 1000),
            ])->throw()->json();

        return LiveProviderSubscription::query()->create([
            'connection_id' => $connection->id,
            'provider' => 'google_meet',
            'channel_id' => $channelId,
            'resource_id' => $response['resourceId'] ?? null,
            'resource_uri' => $response['resourceUri'] ?? null,
            'expires_at' => isset($response['expiration']) ? now()->setTimestamp((int) floor(((int) $response['expiration']) / 1000)) : $expiresAt,
            'last_renewed_at' => now(),
            'status' => 'active',
        ]);
    }
}
