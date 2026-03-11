<?php

return [
    // Désactivation du cache : chaque refresh lit la DB (home, hubs, catégories). Remettre env('VIVAT_DISABLE_PAGE_CACHE', true) pour réactiver le cache via .env
    'disable_page_cache' => true,

    /**
     * Images Unsplash de repli par catégorie (articles sans photo).
     * 20 IDs vérifiés (images.unsplash.com) — plus de variété entre les articles.
     * Format : https://images.unsplash.com/photo-{id}?w=800&h=600&fit=crop&q=80
     */
    'unsplash_fallback_ids' => [
        '1524758631624-e2822e304c36', '1502672260266-1c1ef2d93688', '1517694712202-14dd9538aa97',
        '1513694203232-719a280e022f', '1506929562872-bb421503ef21', '1470071459604-3b5ec3a7fe05',
        '1518495973542-4542c06a5843', '1472396961693-142e6e269027', '1433086966358-54859d0ed716',
        '1518173946687-a4c8892bbd9f', '1469474968028-56623f02e42e', '1472214103451-9374bd1c798e',
        '1529419412599-7bb870e11810', '1421789665209-c9b2a435e3dc', '1505142468610-359e7d316be0',
        '1513836279014-a89f7a76ae86', '1426604966848-d7adac402bff', '1501854140801-50d01698950b',
        '1509316975850-ff9c5deb0cd9', '1441974231531-c6227db76b6e',
    ],
    'unsplash_fallback_by_category' => [
        'energie' => null,
        'sante' => null,
        'technologie' => null,
        'finance' => null,
        'mode' => null,
        'famille' => null,
        'voyage' => null,
        'chez-soi' => null,
        'au-quotidien' => null,
        'default' => null,
    ],

    'home_cache_ttl' => (int) env('VIVAT_HOME_CACHE_TTL', 1800), // 30 min par défaut
    'home_featured_count' => (int) env('VIVAT_HOME_FEATURED_COUNT', 4),
    'home_latest_count' => (int) env('VIVAT_HOME_LATEST_COUNT', 12),
    'home_categories_count' => (int) env('VIVAT_HOME_CATEGORIES_COUNT', 9),
    'writer_signup_url' => env('VIVAT_WRITER_SIGNUP_URL', '/register'),
    'writer_dashboard_url' => env('VIVAT_WRITER_DASHBOARD_URL', '/contributor/submissions'),
];
