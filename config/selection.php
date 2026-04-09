<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Profil de pondération (sélection d'articles)
    |--------------------------------------------------------------------------
    | default | actu_focus | seo_focus | long_form_focus
    | actu_focus = plus de poids sur la fraîcheur (connecté à l'actu)
    | seo_focus = plus de poids sur le potentiel SEO
    | long_form_focus = favorise les sujets multi-sources pour articles de fond
    */
    'weight_profile' => env('SELECTION_WEIGHT_PROFILE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Poids des critères (total = 100)
    |--------------------------------------------------------------------------
    | Règles prédéfinies : l'IA fait un choix basé sur ces poids.
    | freshness : article récent = plus pertinent (connecté à l'actu)
    | quality : qualité du contenu extrait
    | seo : potentiel SEO (mots-clés longue traîne, concurrence)
    | diversity : multi-sources = plus de valeur
    | topic_frequency : si beaucoup d'articles sur le même sujet (ex: 10/50),
    |   ce sujet est plus important → bonus (corrélation, tendance)
    */
    'weights' => [
        'default' => [
            'freshness' => 25,
            'quality' => 25,
            'seo' => 30,
            'diversity' => 15,
            'topic_frequency' => 5,
        ],
        'actu_focus' => [
            'freshness' => 35,
            'quality' => 20,
            'seo' => 25,
            'diversity' => 10,
            'topic_frequency' => 10,
        ],
        'seo_focus' => [
            'freshness' => 15,
            'quality' => 25,
            'seo' => 40,
            'diversity' => 10,
            'topic_frequency' => 10,
        ],
        'long_form_focus' => [
            'freshness' => 15,
            'quality' => 30,
            'seo' => 25,
            'diversity' => 20,
            'topic_frequency' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Seuils de fraîcheur (pour score + type d'article)
    |--------------------------------------------------------------------------
    */
    'freshness' => [
        'decay_days' => 7,           // Après 7 jours, score fraîcheur = 0
        'hot_news_hours' => 48,     // < 48h = hot news (brève)
        'recent_days' => 3,         // < 3 jours = récent
    ],

    /*
    |--------------------------------------------------------------------------
    | Type d'article et longueur cible (contexte pour l'IA)
    |--------------------------------------------------------------------------
    | hot_news : brève d'actualité, percutante, peu de mots
    | long_form : article de fond, analyse, plus de mots
    | L'IA reçoit ce contexte pour adapter ton et nombre de mots.
    */
    'article_types' => [
        'hot_news' => [
            'label' => 'Brève / actualité chaude',
            'min_words' => 400,
            'max_words' => 650,
            'tone' => 'percutant, direct, factuel. Titre accrocheur. Pas de développement long.',
        ],
        'long_form' => [
            'label' => 'Article de fond',
            'min_words' => 1000,
            'max_words' => 1800,
            'tone' => 'approfondi, analytique, structuré avec sous-parties. Contexte et mise en perspective.',
        ],
        'standard' => [
            'label' => 'Standard',
            'min_words' => 800,
            'max_words' => 1200,
            'tone' => 'professionnel et accessible. Équilibre entre information et lisibilité.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bonus "fréquence du sujet" (corrélation)
    |--------------------------------------------------------------------------
    | Si sur N articles du pool, M traitent du même sujet (cluster) :
    | → ce sujet est plus important (tendance). On ajoute un bonus au score.
    | max_bonus = plafond pour ne pas tout écraser par un seul mega-sujet.
    | ratio_threshold = seuil (ex: 0.15 = 15% du pool) pour commencer le bonus.
    */
    'topic_frequency' => [
        'max_bonus' => 20,           // Points max ajoutés au score du topic
        'ratio_threshold' => 0.10,   // 10% du pool = sujet qui ressort
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Clustering (regroupement par sujet)
    |--------------------------------------------------------------------------
    */
    'clustering' => [
        'min_items_per_topic' => 2,
        'max_items_per_topic' => 5,
        'similarity_threshold' => 0.08,  // Jaccard >= 8% = même sujet (permissif pour maximiser les clusters à 2+ sources)
    ],

];
