<?php

return [
    'bot_protection' => [
        'enabled_in_production' => true,
        'enabled_in_local' => (bool) env('SECURITY_BOT_PROTECTION_LOCAL', false),

        // Intentionnellement prudente : on ne bloque ici que les user-agents
        // clairement automatisés ou offensifs pour éviter les faux positifs.
        'blocked_user_agents' => [
            '/^$/i',
            '/^curl\\//i',
            '/^wget\\//i',
            '/^python-requests/i',
            '/^python-urllib/i',
            '/^Python\\//i',
            '/^Go-http-client/i',
            '/^libwww/i',
            '/^scrapy/i',
            '/^HTTPie/i',
            '/^sqlmap/i',
            '/^nikto/i',
            '/^masscan/i',
            '/^nmap/i',
            '/^zgrab/i',
        ],

        'messages' => [
            'web' => 'Votre requête ne peut pas être traitée pour le moment.',
            'api' => 'Forbidden',
        ],
    ],
];
