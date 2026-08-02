<?php

return [
    'review_minimum_completion_percentage' => (int) env('COURSE_REVIEW_MINIMUM_COMPLETION', 100),
    'duration_minutes' => [
        'minute' => 1,
        'hour' => 60,
        'session' => 45,
        'day' => 480,
        'week' => 2400,
    ],
    'discovery' => [
        'cache_seconds' => (int) env('COURSE_DISCOVERY_CACHE_SECONDS', 600),
        'section_limit' => (int) env('COURSE_DISCOVERY_SECTION_LIMIT', 8),
        'recent_search_limit' => (int) env('COURSE_RECENT_SEARCH_LIMIT', 6),
    ],
];
