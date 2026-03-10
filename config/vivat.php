<?php

return [
    // Mettre à true (ou VIVAT_DISABLE_PAGE_CACHE=true en .env) pour voir les changements DB instantanément
    'disable_page_cache' => (bool) env('VIVAT_DISABLE_PAGE_CACHE', true),

    /**
     * Images Unsplash de repli par catégorie (articles sans photo).
     * Chaque catégorie a plusieurs IDs pour que chaque article ait une image différente mais en rapport avec la rubrique.
     * Format Unsplash : https://images.unsplash.com/photo-{id}?w=800&h=600&fit=crop
     */
    'unsplash_fallback_by_category' => [
        'energie' => [
            '1529467154344-24f42960bcc8', '1473341304174-5f1f3302e1c8', '1509391366360-2f7f09120b61',
            '1559302504-64aae0ca2a3f', '1508514177221-188b1cf16e9d',
            '1529467154344-24f42960bcc8', '1473341304174-5f1f3302e1c8', '1509391366360-2f7f09120b61',
        ],
        'sante' => [
            '1576091160550-2173dba999ef', '1579684385127-9ef89946e0e8', '1559757148-24c2d9a8c4e0',
            '1579154219627-8a0b2d71a36e', '1584515933487-6d88ac2076b6',
            '1576091160550-2173dba999ef', '1579684385127-9ef89946e0e8', '1559757148-24c2d9a8c4e0',
        ],
        'technologie' => [
            '1517694712202-14dd9538aa97', '1498050108023-5243f4b084ea', '1504639725590-34d0984388bd',
            '1461749280684-426804e945ce', '1550751827-4bd374c3f58c',
            '1517694712202-14dd9538aa97', '1498050108023-5243f4b084ea', '1504639725590-34d0984388bd',
        ],
        'finance' => [
            '1554224311-beee460c201b', '1551836024-dc7a2c4e4702', '1563013542991-37f698a8aafe',
            '1579621970565-ebf84f3662be', '1586528116311-ad8df39d191b',
            '1554224311-beee460c201b', '1551836024-dc7a2c4e4702', '1563013542991-37f698a8aafe',
        ],
        'mode' => [
            '1551488831765-8a610c0c4f6e', '1515886657613-9f3515b0c78f', '1490481651871-ab68de25d43d',
            '1523381210434-271e8ee2bdf0', '1509631179647-843733d52571',
            '1551488831765-8a610c0c4f6e', '1515886657613-9f3515b0c78f', '1490481651871-ab68de25d43d',
        ],
        'famille' => [
            '1511895426320-d9f5f9b4129d', '1516628850451-3d643d152ee5', '1519689680052-0ebf745d0c4c',
            '1522771739844-6a9f47d92469', '1544771529-4a990c2e0c2b',
            '1511895426320-d9f5f9b4129d', '1516628850451-3d643d152ee5', '1519689680052-0ebf745d0c4c',
        ],
        'voyage' => [
            '1506929562872-bb421503ef21', '1469851692958-3930a789bd15', '1476519949062-2b8d4dc65e55',
            '1507525428034-b723cf961d3e', '1519689680052-0ebf745d0c4c',
            '1506929562872-bb421503ef21', '1469851692958-3930a789bd15', '1476519949062-2b8d4dc65e55',
        ],
        'chez-soi' => [
            '1524758631624-e2822e304c36', '1502672260266-1c1ef2d93688', '1560448204-e02f43698675',
            '1484101408626-506ef349c1c0', '1502672260266-1c1ef2d93689',
            '1524758631624-e2822e304c36', '1560448204-e02f43698675', '1484101408626-506ef349c1c0',
        ],
        'au-quotidien' => [
            '1497366216548-692602d81549', '1502672260266-1c1ef2d93688', '1524758631624-e2822e304c36',
            '1556911220-bff31c812beb', '1513694203232-719a280e022f',
            '1497366216548-692602d81549', '1556911220-bff31c812beb', '1513694203232-719a280e022f',
        ],
        'default' => [
            '1524758631624-e2822e304c36', '1497366216548-692602d81549', '1502672260266-1c1ef2d93688',
            '1556911220-bff31c812beb', '1513694203232-719a280e022f',
            '1524758631624-e2822e304c36', '1497366216548-692602d81549', '1502672260266-1c1ef2d93688',
        ],
    ],

    'home_cache_ttl' => (int) env('VIVAT_HOME_CACHE_TTL', 1800), // 30 min par défaut
    'home_featured_count' => (int) env('VIVAT_HOME_FEATURED_COUNT', 4),
    'home_latest_count' => (int) env('VIVAT_HOME_LATEST_COUNT', 12),
    'home_categories_count' => (int) env('VIVAT_HOME_CATEGORIES_COUNT', 9),
    'writer_signup_url' => env('VIVAT_WRITER_SIGNUP_URL', '/register'),
    'writer_dashboard_url' => env('VIVAT_WRITER_DASHBOARD_URL', '/contributor/submissions'),
];
