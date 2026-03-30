<?php

return [
    'fetch_rss' => [
        'enabled' => true,
        'label' => 'Fetch des flux RSS',
        'description' => 'Récupère automatiquement les flux RSS à rafraîchir et les envoie dans la queue rss.',
        'cadence' => 'Toutes les 30 minutes',
    ],

    'enrich_items' => [
        'enabled' => true,
        'label' => 'Enrichissement IA',
        'description' => 'Prend les items RSS nouveaux et les envoie dans la queue enrichment pour extraction + analyse IA.',
        'cadence' => 'Toutes les heures',
        'limit' => 50,
        'delay_seconds' => 3,
    ],

    'generate_daily_article' => [
        'enabled' => true,
        'label' => 'Génération quotidienne',
        'description' => 'Sélectionne automatiquement la meilleure proposition IA du moment et lance 1 génération de brouillon par jour.',
        'cadence' => 'Chaque jour à 08:00',
        'time' => '08:00',
        'count' => 1,
    ],

    'horizon_snapshot' => [
        'enabled' => true,
        'label' => 'Snapshot Horizon',
        'description' => 'Met à jour les métriques Horizon pour le monitoring des queues.',
        'cadence' => 'Toutes les 5 minutes',
    ],

    'prune_failed_jobs' => [
        'enabled' => true,
        'label' => 'Nettoyage des failed jobs',
        'description' => 'Supprime les jobs échoués vieux de plus de 7 jours.',
        'cadence' => 'Chaque jour',
        'hours' => 168,
    ],
];
