<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Str;
use RuntimeException;

final class TurnCredentialService
{
    public function __construct(private readonly TurnServerHealthService $health) {}

    public function issue(int $sessionId, string $participantKey): array
    {
        $static = config('live-media.static_ice_servers', []);
        $nodes = $this->health->availableNodes();
        if ($nodes === []) {
            return ['ice_servers' => $static, 'expires_in' => 0, 'turn_available' => false];
        }

        $secret = (string) config('live-media.turn.auth_secret');
        if (mb_strlen($secret) < 32) {
            throw new RuntimeException('LIVE_MEDIA_TURN_AUTH_SECRET must contain at least 32 characters.');
        }

        $ttl = max(60, min(3600, (int) config('live-media.turn.credential_ttl_seconds', 600)));
        $expiresAt = now()->addSeconds($ttl)->timestamp;
        $identity = preg_replace('/[^A-Za-z0-9:_-]/', '', $participantKey);
        $username = $expiresAt.':'.$sessionId.':'.$identity.':'.Str::lower(Str::random(12));
        $credential = base64_encode(hash_hmac('sha1', $username, $secret, true));
        $turnServers = array_map(fn (array $node): array => [
            'urls' => $node['urls'],
            'username' => $username,
            'credential' => $credential,
            'credentialType' => 'password',
        ], $nodes);

        return [
            'ice_servers' => [...$static, ...$turnServers],
            'expires_in' => $ttl,
            'turn_available' => true,
            'max_bitrate_kbps' => max(128, (int) config('live-media.turn.max_bitrate_kbps', 2500)),
        ];
    }
}
