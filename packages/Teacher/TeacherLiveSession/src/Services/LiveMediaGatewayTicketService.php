<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Str;
use RuntimeException;

final class LiveMediaGatewayTicketService
{
    public function issue(int $sessionId, string $participantKey, string $role, ?int $breakoutRoomId = null, ?string $displayName = null): array
    {
        $secret = (string) config('live-media.gateway.secret');
        if ($secret === '') {
            throw new RuntimeException('LIVE_MEDIA_GATEWAY_SECRET is required when SFU topology is enabled.');
        }

        $ttl = max(30, min(300, (int) config('live-media.gateway.ticket_ttl_seconds', 120)));
        $now = now()->timestamp;
        $claims = [
            'iss' => (string) config('app.url'),
            'aud' => 'mindigo-live-media',
            'session_id' => $sessionId,
            'participant_key' => $participantKey,
            'role' => $role,
            'display_name' => mb_substr(trim((string) $displayName), 0, 120),
            'breakout_room_id' => $breakoutRoomId,
            'iat' => $now,
            'exp' => $now + $ttl,
            'jti' => (string) Str::uuid(),
        ];
        $payload = $this->encode(json_encode($claims, JSON_THROW_ON_ERROR));
        $signature = $this->encode(hash_hmac('sha256', $payload, $secret, true));

        return ['ticket' => $payload.'.'.$signature, 'expires_in' => $ttl];
    }

    private function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
