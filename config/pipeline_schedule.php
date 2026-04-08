<?php

return [
    'fetch_rss' => [
        'enabled' => true,
        'label' => 'Fetch des flux RSS',
        'description' => 'Récupère automatiquement les flux RSS à rafraîchir et les envoie dans la queue rss.',
        'cadence' => 'Toutes les 6 heures',
    ],

    'enrich_items' => [
        'enabled' => true,
        'label' => 'Enrichissement IA',
        'description' => 'Prend les items RSS nouveaux et les envoie dans la queue enrichment pour extraction + analyse IA.',
        'cadence' => 'Chaque jour à 06:30',
        'limit' => 3,
        'delay_seconds' => 10,
        'time' => '06:30',
    ],

    'generate_daily_article' => [
        'enabled' => true,
        'label' => 'Génération quotidienne',
        'description' => 'Sélectionne automatiquement la meilleure proposition IA du moment et lance 1 génération de brouillon par jour.',
        'cadence' => 'Chaque jour à 08:00',
        'time' => '10:00',
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

    'monitoring' => [
        'enabled' => true,
        'label' => 'Surveillance pipeline',
        'description' => 'Contrôle régulier du silence pipeline et de la fraîcheur Horizon.',
        'cadence' => 'Toutes les 2 heures',
        'enrichment_silence_hours' => 4,
        'alert_cooldown_hours' => 4,
        'horizon_stale_minutes' => 15,
    ],
];
