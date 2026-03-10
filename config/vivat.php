<?php

return [
    'home_cache_ttl' => (int) env('VIVAT_HOME_CACHE_TTL', 1800), // 30 min par défaut
    'home_featured_count' => (int) env('VIVAT_HOME_FEATURED_COUNT', 4),
    'home_latest_count' => (int) env('VIVAT_HOME_LATEST_COUNT', 12),
    'home_categories_count' => (int) env('VIVAT_HOME_CATEGORIES_COUNT', 9),
    'writer_signup_url' => env('VIVAT_WRITER_SIGNUP_URL', '/register'),
    'writer_dashboard_url' => env('VIVAT_WRITER_DASHBOARD_URL', '/contributor/submissions'),
];
