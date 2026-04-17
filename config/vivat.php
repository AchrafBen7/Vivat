<?php

return [
    // Mettre à true dans .env pour bypass le cache public pendant un debug local.
    'disable_page_cache' => (bool) env('VIVAT_DISABLE_PAGE_CACHE', false),
    'public_cache_prefix' => (string) env('VIVAT_PUBLIC_CACHE_PREFIX', 'vivat.public'),
    // La home SSR passe par ce TTL générique via PublicPageDataService.
    'public_cache_ttl' => (int) env('VIVAT_PUBLIC_CACHE_TTL', 60),
    'admin_alert_email' => (string) env('VIVAT_ADMIN_ALERT_EMAIL', env('MAIL_FROM_ADDRESS', 'info@mediaa.be')),

    /**
     * Images Pexels de repli par catégorie (articles sans photo).
     * On stocke des URLs complètes pour garder la main sur les visuels.
     */
    /**
     * Images Pexels de repli chaque URL ne doit jamais être réutilisée sur une même page.
     * Pool élargi pour garantir l'unicité.
     */
    'pexels_fallback_urls' => [
        'default' => [
            'https://images.pexels.com/photos/417074/pexels-photo-417074.jpeg',
            'https://images.pexels.com/photos/414171/pexels-photo-414171.jpeg',
            'https://images.pexels.com/photos/1563356/pexels-photo-1563356.jpeg',
            'https://images.pexels.com/photos/210243/pexels-photo-210243.jpeg',
            'https://images.pexels.com/photos/34950/pexels-photo.jpg',
            'https://images.pexels.com/photos/572897/pexels-photo-572897.jpeg',
            'https://images.pexels.com/photos/1181244/pexels-photo-1181244.jpeg',
            'https://images.pexels.com/photos/1261728/pexels-photo-1261728.jpeg',
            'https://images.pexels.com/photos/1323550/pexels-photo-1323550.jpeg',
            'https://images.pexels.com/photos/1643383/pexels-photo-1643383.jpeg',
            'https://images.pexels.com/photos/2253879/pexels-photo-2253879.jpeg',
            'https://images.pexels.com/photos/255379/pexels-photo-255379.jpeg',
            'https://images.pexels.com/photos/2662116/pexels-photo-2662116.jpeg',
            'https://images.pexels.com/photos/3184292/pexels-photo-3184292.jpeg',
            'https://images.pexels.com/photos/386009/pexels-photo-386009.jpeg',
        ],
        'energie' => [
            'https://images.pexels.com/photos/356036/pexels-photo-356036.jpeg',
            'https://images.pexels.com/photos/885350/pexels-photo-885350.jpeg',
        ],
        'sante' => [
            'https://images.pexels.com/photos/40568/medical-appointment-doctor-healthcare-40568.jpeg',
            'https://images.pexels.com/photos/4386466/pexels-photo-4386466.jpeg',
        ],
        'technologie' => [
            'https://images.pexels.com/photos/373543/pexels-photo-373543.jpeg',
            'https://images.pexels.com/photos/1181244/pexels-photo-1181244.jpeg',
        ],
        'finance' => [
            'https://images.pexels.com/photos/4386370/pexels-photo-4386370.jpeg',
            'https://images.pexels.com/photos/210607/pexels-photo-210607.jpeg',
        ],
        'mode' => [
            'https://images.pexels.com/photos/934070/pexels-photo-934070.jpeg',
            'https://images.pexels.com/photos/7679720/pexels-photo-7679720.jpeg',
        ],
        'famille' => [
            'https://images.pexels.com/photos/2253879/pexels-photo-2253879.jpeg',
            'https://images.pexels.com/photos/1257110/pexels-photo-1257110.jpeg',
        ],
        'voyage' => [
            'https://images.pexels.com/photos/3155666/pexels-photo-3155666.jpeg',
            'https://images.pexels.com/photos/386009/pexels-photo-386009.jpeg',
        ],
        'chez-soi' => [
            'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg',
            'https://images.pexels.com/photos/1643383/pexels-photo-1643383.jpeg',
        ],
        'au-quotidien' => [
            'https://images.pexels.com/photos/3768916/pexels-photo-3768916.jpeg',
            'https://images.pexels.com/photos/4050291/pexels-photo-4050291.jpeg',
        ],
    ],

    // Utilisé par l'API home. Gardé court pour éviter un staging trop collant.
    'home_cache_ttl' => (int) env('VIVAT_HOME_CACHE_TTL', 60),

    /**
     * Préfixe des clés de cache Laravel pour la home (clé complète : {prefix}.fr / {prefix}.nl).
     * Changer la valeur (ou VIVAT_HOME_CACHE_KEY_PREFIX dans .env) invalide les entrées mises en cache avant un changement de données.
     */
    'home_cache_key_prefix' => (string) env('VIVAT_HOME_CACHE_KEY_PREFIX', 'vivat.home.v2'),
    'home_featured_count' => (int) env('VIVAT_HOME_FEATURED_COUNT', 4),
    'home_latest_count' => (int) env('VIVAT_HOME_LATEST_COUNT', 12),
    'home_categories_count' => (int) env('VIVAT_HOME_CATEGORIES_COUNT', 9),

    /**
     * Fichiers dans public/ : nom sans extension = clé (slug catégorie ou alias ci-dessous).
     * Ex. public/finance.jpg, public/mode.mp4, chez-soi → chezsoi.jpg
     *
     * @var array<string, string>
     */
    'category_media_slug_map' => [
        'chez-soi' => 'chezsoi',
        'au-quotidien' => 'quotidien',
    ],

    'writer_signup_url' => env('VIVAT_WRITER_SIGNUP_URL', '/devenir-redacteur'),
    'writer_dashboard_url' => env('VIVAT_WRITER_DASHBOARD_URL', '/contributor/submissions'),

    'adsense' => [
        'client' => 'ca-pub-4283192845913527',
        'slots' => [
            'home_tablet_banner_728x90' => [
                'slot' => '4377137008',
                'mode' => 'fixed',
                'width' => 728,
                'height' => 90,
            ],
            'home_sidebar_300x600' => [
                'slot' => '9711343832',
                'mode' => 'fixed',
                'width' => 300,
                'height' => 600,
            ],
            'home_bottom_banner_970x250' => [
                'slot' => '9703604977',
                'mode' => 'fixed',
                'width' => 970,
                'height' => 250,
            ],
            'article_top_banner_728x90' => [
                'slot' => '5035612553',
                'mode' => 'fixed',
                'width' => 728,
                'height' => 90,
            ],
            'article_mid_banner_970x250' => [
                'slot' => '6101282216',
                'mode' => 'fixed',
                'width' => 970,
                'height' => 250,
            ],
            'article_related_square_336x280' => [
                'slot' => '6620156960',
                'mode' => 'fixed',
                'width' => 336,
                'height' => 280,
            ],
            'about_mobile_banner_320x50' => [
                'slot' => '8535873863',
                'mode' => 'fixed',
                'width' => 320,
                'height' => 50,
            ],
            'about_desktop_banner_728x90' => [
                'slot' => '7222792194',
                'mode' => 'fixed',
                'width' => 728,
                'height' => 90,
            ],
            'about_bottom_banner_970x250' => [
                'slot' => '3283547186',
                'mode' => 'fixed',
                'width' => 970,
                'height' => 250,
            ],
            'category_top_banner_728x90' => [
                'slot' => '4213485475',
                'mode' => 'fixed',
                'width' => 728,
                'height' => 90,
            ],
            'category_mid_banner_970x250' => [
                'slot' => '6456505433',
                'mode' => 'fixed',
                'width' => 970,
                'height' => 250,
            ],
        ],
    ],
];
