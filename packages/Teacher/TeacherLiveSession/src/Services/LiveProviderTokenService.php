<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\Http;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Models\LiveProviderConnection;
use RuntimeException;

final class LiveProviderTokenService
{
    public function accessToken(int $userId, LiveSessionProvider $provider): string
    {
        $connection = LiveProviderConnection::query()->where('user_id', $userId)->where('provider', $provider->value)->first();
        if (! $connection?->isUsable()) {
            throw new RuntimeException('External meeting provider is not connected.');
        }

        if (! $connection->expires_at || $connection->expires_at->isAfter(now()->addMinute())) {
            return $connection->access_token;
        }
        if (blank($connection->refresh_token)) {
            throw new RuntimeException('External meeting provider token has expired.');
        }

        $config = config("live-providers.{$provider->value}");
        $request = Http::asForm()->acceptJson();
        $payload = ['grant_type' => 'refresh_token', 'refresh_token' => $connection->refresh_token];
        if ($provider === LiveSessionProvider::Zoom) {
            $request = $request->withBasicAuth($config['client_id'], $config['client_secret']);
        } else {
            $payload += ['client_id' => $config['client_id'], 'client_secret' => $config['client_secret']];
        }

        $tokens = $request->post($config['token_url'], $payload)->throw()->json();
        $connection->update([
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'] ?? $connection->refresh_token,
            'expires_at' => now()->addSeconds((int) ($tokens['expires_in'] ?? 3600)),
            'last_refreshed_at' => now(),
        ]);

        return $tokens['access_token'];
    }
}
