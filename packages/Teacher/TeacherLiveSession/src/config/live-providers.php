<?php

return [
    'http' => [
        'timeout' => (int) env('LIVE_PROVIDER_HTTP_TIMEOUT', 10),
        'connect_timeout' => (int) env('LIVE_PROVIDER_CONNECT_TIMEOUT', 3),
        'retries' => (int) env('LIVE_PROVIDER_HTTP_RETRIES', 2),
    ],
    'circuit_breaker' => [
        'failure_threshold' => (int) env('LIVE_PROVIDER_CIRCUIT_FAILURE_THRESHOLD', 3),
        'cooldown_seconds' => (int) env('LIVE_PROVIDER_CIRCUIT_COOLDOWN', 300),
    ],
    'google_meet' => [
        'client_id' => env('GOOGLE_MEET_CLIENT_ID'),
        'client_secret' => env('GOOGLE_MEET_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_MEET_REDIRECT_URI'),
        'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'revoke_url' => 'https://oauth2.googleapis.com/revoke',
        'scopes' => [
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/meetings.space.readonly',
            'https://www.googleapis.com/auth/meetings.space.created',
        ],
        'webhook_token' => env('GOOGLE_MEET_WEBHOOK_TOKEN'),
        'pubsub_verification_token' => env('GOOGLE_MEET_PUBSUB_VERIFICATION_TOKEN'),
        'calendar_webhook_url' => env('GOOGLE_CALENDAR_WEBHOOK_URL', env('APP_URL').'/webhooks/live-providers/google-calendar'),
        'watch_ttl_seconds' => (int) env('GOOGLE_CALENDAR_WATCH_TTL_SECONDS', 604800),
    ],
    'zoom' => [
        'client_id' => env('ZOOM_CLIENT_ID'),
        'client_secret' => env('ZOOM_CLIENT_SECRET'),
        'redirect_uri' => env('ZOOM_REDIRECT_URI'),
        'authorize_url' => 'https://zoom.us/oauth/authorize',
        'token_url' => 'https://zoom.us/oauth/token',
        'revoke_url' => 'https://zoom.us/oauth/revoke',
        'scopes' => array_values(array_filter(explode(' ', (string) env('ZOOM_OAUTH_SCOPES', 'meeting:write:meeting meeting:read:meeting')))),
        'webhook_secret' => env('ZOOM_WEBHOOK_SECRET'),
        'webhook_tolerance_seconds' => (int) env('ZOOM_WEBHOOK_TOLERANCE_SECONDS', 300),
        'webhook_url' => env('ZOOM_WEBHOOK_URL', env('APP_URL').'/webhooks/live-providers/zoom'),
    ],
];
