<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Enums\LiveParticipantRole;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Throwable;

final class LiveSessionJoinTokenService
{
    private const TTL_SECONDS = 600;

    public function issue(LiveSession $session, User $user, LiveParticipantRole $role): string
    {
        return Crypt::encryptString(json_encode([
            'session_id' => (int) $session->getKey(),
            'user_id' => (int) $user->getKey(),
            'role' => $role->value,
            'version' => (int) ($session->join_token_version ?? 1),
            'issued_at' => now()->timestamp,
            'expires_at' => now()->addSeconds(self::TTL_SECONDS)->timestamp,
        ], JSON_THROW_ON_ERROR));
    }

    public function validate(string $token, LiveSession $session, User $user): array
    {
        try {
            $claims = json_decode(Crypt::decryptString($token), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            throw ValidationException::withMessages(['join_token' => __('teacher-live-session::app.validation.invalid_join_token')]);
        }

        $valid = (int) ($claims['session_id'] ?? 0) === (int) $session->getKey()
            && (int) ($claims['user_id'] ?? 0) === (int) $user->getKey()
            && (int) ($claims['version'] ?? 0) === (int) $session->join_token_version
            && (int) ($claims['expires_at'] ?? 0) >= now()->timestamp;

        if (! $valid) {
            throw ValidationException::withMessages(['join_token' => __('teacher-live-session::app.validation.invalid_join_token')]);
        }

        return $claims;
    }
}
