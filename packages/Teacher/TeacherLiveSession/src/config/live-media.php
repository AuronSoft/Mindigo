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
    'ice_servers' => array_values(array_filter([
        env('LIVE_MEDIA_STUN_URL') ? ['urls' => [env('LIVE_MEDIA_STUN_URL')]] : null,
        env('LIVE_MEDIA_TURN_URL') ? [
            'urls' => [env('LIVE_MEDIA_TURN_URL')],
            'username' => env('LIVE_MEDIA_TURN_USERNAME'),
            'credential' => env('LIVE_MEDIA_TURN_CREDENTIAL'),
        ] : null,
    ])),
];
