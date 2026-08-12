<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Models\LiveProviderConnection;
use RuntimeException;

final class LiveProviderOAuthService
{
    public function authorizationUrl(LiveSessionProvider $provider): string
    {
        $config = $this->configured($provider);
        $state = Str::random(64);
        session()->put("live_provider_oauth.{$provider->value}", ['state' => $state, 'issued_at' => now()->timestamp]);
        $query = ['client_id' => $config['client_id'], 'redirect_uri' => $config['redirect_uri'], 'response_type' => 'code', 'state' => $state, 'scope' => implode(' ', $config['scopes'])];
        if ($provider === LiveSessionProvider::GoogleMeet) {
            $query += ['access_type' => 'offline', 'prompt' => 'consent', 'include_granted_scopes' => 'true'];
        }

        return $config['authorize_url'].'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    public function connect(User $user, LiveSessionProvider $provider, string $code, string $state): LiveProviderConnection
    {
        $stored = session()->pull("live_provider_oauth.{$provider->value}");
        if (! is_array($stored) || now()->timestamp - ($stored['issued_at'] ?? 0) > 600 || ! hash_equals((string) ($stored['state'] ?? ''), $state)) {
            throw new RuntimeException('Invalid or expired OAuth state.');
        }
        $config = $this->configured($provider);
        $request = Http::asForm()->acceptJson()
            ->connectTimeout(config('live-providers.http.connect_timeout', 3))
            ->timeout(config('live-providers.http.timeout', 10))
            ->retry(config('live-providers.http.retries', 2), 200);
        $payload = ['code' => $code, 'grant_type' => 'authorization_code', 'redirect_uri' => $config['redirect_uri']];
        if ($provider === LiveSessionProvider::Zoom) {
            $request = $request->withBasicAuth($config['client_id'], $config['client_secret']);
        } else {
            $payload += ['client_id' => $config['client_id'], 'client_secret' => $config['client_secret']];
        }
        $tokens = $request->post($config['token_url'], $payload)->throw()->json();
        $existing = LiveProviderConnection::query()->where('user_id', $user->getKey())->where('provider', $provider->value)->first();

        return LiveProviderConnection::query()->updateOrCreate(
            ['user_id' => $user->getKey(), 'provider' => $provider->value],
            ['access_token' => $tokens['access_token'], 'refresh_token' => $tokens['refresh_token'] ?? $existing?->refresh_token,
                'scopes' => isset($tokens['scope']) ? preg_split('/\s+/', trim($tokens['scope'])) : $config['scopes'],
                'expires_at' => now()->addSeconds((int) ($tokens['expires_in'] ?? 3600)), 'revoked_at' => null, 'last_refreshed_at' => now()]
        );
    }

    public function disconnect(User $user, LiveSessionProvider $provider): void
    {
        LiveProviderConnection::query()->where('user_id', $user->getKey())->where('provider', $provider->value)->delete();
    }

    public function isConfigured(LiveSessionProvider $provider): bool
    {
        $config = config("live-providers.{$provider->value}", []);

        return filled($config['client_id'] ?? null) && filled($config['client_secret'] ?? null) && filled($config['redirect_uri'] ?? null);
    }

    private function configured(LiveSessionProvider $provider): array
    {
        if (! $provider->isExternal() || ! $this->isConfigured($provider)) {
            throw new RuntimeException('Provider OAuth is not configured.');
        }

        return config("live-providers.{$provider->value}");
    }
}
