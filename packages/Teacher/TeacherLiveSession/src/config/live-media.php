<?php

return [
    'topology' => env('LIVE_MEDIA_TOPOLOGY', 'mesh'),
    'sfu_health_url' => env('LIVE_MEDIA_SFU_HEALTH_URL'),
    'gateway' => [
        'public_url' => env('LIVE_MEDIA_GATEWAY_URL', 'ws://127.0.0.1:8090'),
        'health_url' => env('LIVE_MEDIA_GATEWAY_HEALTH_URL', 'http://127.0.0.1:8091/health'),
        'secret' => env('LIVE_MEDIA_GATEWAY_SECRET'),
        'ticket_ttl_seconds' => (int) env('LIVE_MEDIA_GATEWAY_TICKET_TTL', 120),
    ],
    'safe_mesh_capacity' => (int) env('LIVE_MEDIA_SAFE_MESH_CAPACITY', 8),
    'static_ice_servers' => array_values(array_filter([
        env('LIVE_MEDIA_STUN_URL') ? ['urls' => [env('LIVE_MEDIA_STUN_URL')]] : null,
    ])),
    'turn' => [
        'auth_secret' => env('LIVE_MEDIA_TURN_AUTH_SECRET'),
        'realm' => env('LIVE_MEDIA_TURN_REALM', 'mindigo.local'),
        'credential_ttl_seconds' => (int) env('LIVE_MEDIA_TURN_CREDENTIAL_TTL', 600),
        'health_cache_seconds' => (int) env('LIVE_MEDIA_TURN_HEALTH_CACHE', 90),
        'fail_open' => env('LIVE_MEDIA_TURN_FAIL_OPEN', true),
        'max_bitrate_kbps' => (int) env('LIVE_MEDIA_TURN_MAX_BITRATE_KBPS', 2500),
        'nodes' => array_values(array_filter([
            env('LIVE_MEDIA_TURN_URLS') ? [
                'id' => 'primary',
                'urls' => array_values(array_filter(array_map('trim', explode(',', env('LIVE_MEDIA_TURN_URLS'))))),
                'health_url' => env('LIVE_MEDIA_TURN_HEALTH_URL'),
            ] : null,
            env('LIVE_MEDIA_TURN_FAILOVER_URLS') ? [
                'id' => 'failover',
                'urls' => array_values(array_filter(array_map('trim', explode(',', env('LIVE_MEDIA_TURN_FAILOVER_URLS'))))),
                'health_url' => env('LIVE_MEDIA_TURN_FAILOVER_HEALTH_URL'),
            ] : null,
        ])),
    ],
];
