<?php

namespace App\Support\LiveSession;

use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;

final class ExternalMeetingUrlPolicy
{
    private const HOSTS = [
        'google_meet' => ['meet.google.com'],
        'zoom' => ['zoom.us'],
    ];

    public function allows(LiveSessionProvider $provider, mixed $url): bool
    {
        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower(rtrim((string) parse_url($url, PHP_URL_HOST), '.'));
        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        return collect(self::HOSTS[$provider->value] ?? [])->contains(
            fn (string $allowed) => $host === $allowed || str_ends_with($host, '.'.$allowed),
        );
    }
}
