<?php

return [
    'disk' => env('LIVE_DR_DISK', 'local'),
    'prefix' => trim(env('LIVE_DR_PREFIX', 'disaster-recovery/live-sessions'), '/'),
    'encryption_key' => env('LIVE_DR_ENCRYPTION_KEY'),
    'retention_copies' => (int) env('LIVE_DR_RETENTION_COPIES', 14),
    'include_media' => (bool) env('LIVE_DR_INCLUDE_MEDIA', true),
    'verify_after_backup' => (bool) env('LIVE_DR_VERIFY_AFTER_BACKUP', true),
    'require_offsite_in_production' => (bool) env('LIVE_DR_REQUIRE_OFFSITE', true),
];
