<?php

return [
    'ice_servers' => array_values(array_filter([
        env('LIVE_MEDIA_STUN_URL') ? ['urls' => [env('LIVE_MEDIA_STUN_URL')]] : null,
        env('LIVE_MEDIA_TURN_URL') ? [
            'urls' => [env('LIVE_MEDIA_TURN_URL')],
            'username' => env('LIVE_MEDIA_TURN_USERNAME'),
            'credential' => env('LIVE_MEDIA_TURN_CREDENTIAL'),
        ] : null,
    ])),
];
