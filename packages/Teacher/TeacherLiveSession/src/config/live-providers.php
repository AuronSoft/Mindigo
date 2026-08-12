<?php

return [
    'google_meet' => [
        'client_id' => env('GOOGLE_MEET_CLIENT_ID'),
        'client_secret' => env('GOOGLE_MEET_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_MEET_REDIRECT_URI'),
        'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'revoke_url' => 'https://oauth2.googleapis.com/revoke',
        'scopes' => ['https://www.googleapis.com/auth/calendar.events'],
    ],
    'zoom' => [
        'client_id' => env('ZOOM_CLIENT_ID'),
        'client_secret' => env('ZOOM_CLIENT_SECRET'),
        'redirect_uri' => env('ZOOM_REDIRECT_URI'),
        'authorize_url' => 'https://zoom.us/oauth/authorize',
        'token_url' => 'https://zoom.us/oauth/token',
        'revoke_url' => 'https://zoom.us/oauth/revoke',
        'scopes' => array_values(array_filter(explode(' ', (string) env('ZOOM_OAUTH_SCOPES', 'meeting:write:meeting meeting:read:meeting')))),
    ],
];
