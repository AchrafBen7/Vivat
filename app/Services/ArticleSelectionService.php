<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\RssItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Service de sélection intelligente des articles à générer.
 *
 * Répond à la question : "Pourquoi générer CET article et pas un autre ?"
 *
 * Stratégie :
 *  1. Score chaque item enrichi (fraîcheur, qualité, diversité sources, potentiel SEO, règles prédéfinies)
 *  2. Regroupe les items par sujet (similarité de mots-clés)
 *  3. Bonus "fréquence du sujet" : si beaucoup d'articles sur le même thème (ex: 10/50), ce sujet est prioritaire (corrélation, tendance)
 *  4. Sélectionne les meilleurs groupes ; indique le type d'article attendu (hot_news vs article de fond) et la longueur cible
 *  5. Retourne N propositions avec justification pour que l'IA sache sur quoi se baser
 *
 * Règles et poids : config/selection.php (profils default, actu_focus, seo_focus, long_form_focus)
 */
class ArticleSelectionService
{
    private function getWeights(): array
    {
        $profile = config('selection.weight_profile', 'default');
        $weights = config("selection.weights.{$profile}", config('selection.weights.default'));

        return array_merge([
            'freshness' => 25,
            'quality' => 25,
            'seo' => 30,
            'diversity' => 15,
            'topic_frequency' => 5,
        ], $weights ?? []);
    }

    private function getFreshnessDecayDays(): int
    {
        return (int) (config('selection.freshness.decay_days') ?? 7);
    }

    private function getClusteringConfig(): array
    {
        return array_merge([
            'min_items_per_topic' => 2,
            'max_items_per_topic' => 5,
            'similarity_threshold' => 0.20,
        ], config('selection.clustering', []));
    }

    /**
     * Sélectionne les meilleures propositions d'articles à générer.
     *
     * @param  int  $count  Nombre d'articles à proposer (ex: 1 par jour)
     * @param  string|null  $categoryId  Filtrer par catégorie (optionnel)
     * @return array<int, array{
     *     topic: string,
     *     score: int,
     *     reasoning: string,
     *     category: array,
     *     seo_keywords: array,
     *     items: array,
     *     source_count: int,
     *     avg_quality: float
     * }>
     */
    public function selectBestTopics(int $count = 1, ?string $categoryId = null): array
    {
        // 1. Récupérer tous les items enrichis disponibles
        $query = RssItem::query()
            ->where('status', 'enriched')
            ->whereHas('enrichedItem')
            ->with(['enrichedItem', 'rssFeed.source', 'category']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            return [];
        }

        // 2. Scorer chaque item individuellement
        $scoredItems = $items->map(fn ($item) => [
            'item' => $item,
            'score' => $this->scoreItem($item),
            'keywords' => $this->extractKeywords($item),
        ]);

        // 3. Regrouper par similarité de sujet (topic clustering)
        $topics = $this->clusterByTopic($scoredItems);
        $totalPoolSize = $items->count();

        // 4. Scorer chaque topic-group (avec bonus "fréquence du sujet" et type d'article suggéré)
        $scoredTopics = collect($topics)->map(function ($group) use ($totalPoolSize) {
            return $this->scoreTopic($group, $totalPoolSize);
        });

        // 5. Trier par score décroissant et prendre les N meilleurs
        $best = $scoredTopics
            ->sortByDesc('score')
            ->take($count)
            ->values()
            ->toArray();

        return $best;
    }

    /**
     * Score un item individuel (0-100) selon les poids configurés (règles prédéfinies).
     */
    private function scoreItem(RssItem $item): int
    {
        $weights = $this->getWeights();
        $decayDays = $this->getFreshnessDecayDays();
        $score = 0;

        // --- Fraîcheur (connecté à l'actu) ---
        $publishedAt = $item->published_at ?? $item->fetched_at ?? $item->created_at;
        if ($publishedAt) {
            $daysOld = now()->diffInDays($publishedAt);
            $freshnessRatio = max(0, 1 - ($daysOld / $decayDays));
            $score += (int) round($freshnessRatio * ($weights['freshness'] ?? 25));
        }

        // --- Qualité enrichissement ---
        $enriched = $item->enrichedItem;
        if ($enriched) {
            $qualityRatio = $enriched->quality_score / 100;
            $score += (int) round($qualityRatio * ($weights['quality'] ?? 25));
            $wordCount = $enriched->getWordCount();
            if ($wordCount >= 1000) {
                $score += 5;
            }
        }

        // --- Potentiel SEO ---
        $keywords = $this->extractKeywords($item);
        $seoScore = $this->estimateSeoScore($keywords, $item->category);
        $score += (int) round(($seoScore / 100) * ($weights['seo'] ?? 30));

        return min(100, $score);
    }

    /**
     * Extrait les mots-clés pertinents d'un item enrichi.
     *
     * @return array<int, array{word: string, frequency: int, seo_weight: int}>
     */
    private function extractKeywords(RssItem $item): array
    {
        $enriched = $item->enrichedItem;
        if (! $enriched) {
            return [];
        }

        // Collecter le texte de tous les champs pertinents
        $text = mb_strtolower(implode(' ', [
            $item->title ?? '',
            $enriched->lead ?? '',
            implode(' ', $enriched->key_points ?? []),
            implode(' ', $enriched->headings ?? []),
        ]));

        // Supprimer la ponctuation et les mots trop courts
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Filtrer les stop words français
        $stopWords = $this->getStopWords();
        $words = array_filter($words, fn ($w) => mb_strlen($w) >= 4 && ! in_array($w, $stopWords));

        // Compter les fréquences
        $freq = array_count_values($words);
        arsort($freq);

        // Prendre les top 15 et évaluer leur poids SEO
        $keywords = [];
        foreach (array_slice($freq, 0, 15, true) as $word => $count) {
            $keywords[] = [
                'word' => $word,
                'frequency' => $count,
                'seo_weight' => $this->estimateKeywordSeoWeight($word),
            ];
        }

        // Trier par SEO weight puis fréquence
        usort($keywords, fn ($a, $b) => ($b['seo_weight'] * 10 + $b['frequency']) <=> ($a['seo_weight'] * 10 + $a['frequency']));

        return $keywords;
    }

    /**
     * Estime le poids SEO d'un mot-clé individuel.
     *
     * Critères heuristiques (sans API externe) :
     *  - Longueur du mot (les mots longs sont souvent plus spécifiques = moins de concurrence)
     *  - Mots composés ou techniques = meilleur potentiel SEO
     *  - Mots génériques = forte concurrence, faible potentiel
     */
    private function estimateKeywordSeoWeight(string $word): int
    {
        $weight = 50; // Base

        // Longueur : les mots de 6-12 caractères sont souvent les meilleurs en SEO
        $len = mb_strlen($word);
        if ($len >= 6 && $len <= 12) {
            $weight += 15;
        } elseif ($len >= 13) {
            $weight += 20; // Termes très spécifiques
        } elseif ($len <= 4) {
            $weight -= 10; // Trop générique
        }

        // Mots-clés à haute valeur SEO (thématique environnement/vivat)
        $highValueTerms = [
            'transition', 'écologique', 'renouvelable', 'biodiversité', 'carbone',
            'durable', 'énergie', 'recyclage', 'pollution', 'climat',
            'empreinte', 'neutralité', 'sobriété', 'permaculture', 'agroécologie',
            'rénovation', 'thermique', 'photovoltaïque', 'hydrogène', 'biomasse',
            'pesticides', 'déforestation', 'compostage', 'mobilité', 'véhicule',
            'électrique', 'consommation', 'responsable', 'zéro déchet',
        ];
        if (in_array($word, $highValueTerms)) {
            $weight += 25;
        }

        // Termes trop génériques (forte concurrence, faible valeur)
        $genericTerms = [
            'article', 'france', 'monde', 'année', 'aussi',
            'plus', 'faire', 'avoir', 'être', 'très', 'tout',
            'comme', 'avec', 'pour', 'dans', 'cette', 'sont',
        ];
        if (in_array($word, $genericTerms)) {
            $weight -= 30;
        }

        return max(0, min(100, $weight));
    }

    /**
     * Estime le score SEO global d'un ensemble de mots-clés pour une catégorie.
     */
    private function estimateSeoScore(array $keywords, ?Category $category): int
    {
        if (empty($keywords)) {
            return 0;
        }

        $totalWeight = 0;
        $count = 0;
        foreach (array_slice($keywords, 0, 5) as $kw) {
            $totalWeight += $kw['seo_weight'];
            $count++;
        }

        // Moyenne pondérée des top 5 mots-clés
        $avgWeight = $count > 0 ? $totalWeight / $count : 0;

        // Bonus si la catégorie est à forte demande SEO
        $highDemandCategories = ['environnement', 'energie', 'sante', 'alimentation', 'technologie'];
        if ($category && in_array($category->slug, $highDemandCategories)) {
            $avgWeight = min(100, $avgWeight + 10);
        }

        return (int) round($avgWeight);
    }

    /**
     * Regroupe les items par similarité de sujet via les mots-clés communs.
     *
     * @param  Collection  $scoredItems  [{item, score, keywords}, ...]
     * @return array<int, Collection>  Groupes d'items par sujet
     */
    private function clusterByTopic(Collection $scoredItems): array
    {
        $groups = [];
        $assigned = [];

        $itemsArray = $scoredItems->values()->all();

        foreach ($itemsArray as $i => $entry) {
            if (in_array($i, $assigned)) {
                continue;
            }

            $group = collect([$entry]);
            $assigned[] = $i;

            $keywordsA = collect($entry['keywords'])->pluck('word')->toArray();

            // Chercher les items similaires
            foreach ($itemsArray as $j => $other) {
                if ($i === $j || in_array($j, $assigned)) {
                    continue;
                }

                $keywordsB = collect($other['keywords'])->pluck('word')->toArray();
                $similarity = $this->keywordSimilarity($keywordsA, $keywordsB);

                $config = $this->getClusteringConfig();
                $threshold = $config['similarity_threshold'] ?? 0.20;
                $maxPerTopic = $config['max_items_per_topic'] ?? 5;

                if ($similarity >= $threshold) {
                    $group->push($other);
                    $assigned[] = $j;
                    if ($group->count() >= $maxPerTopic) {
                        break;
                    }
                }
            }

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Calcule la similarité entre deux ensembles de mots-clés (Jaccard).
     */
    private function keywordSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        $intersection = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));

        return $union > 0 ? $intersection / $union : 0.0;
    }

    /**
     * Score un groupe d'items (un topic potentiel) et produit la proposition.
     * Ajoute : bonus "fréquence du sujet" (si 10/50 articles sur ce sujet = plus important),
     * type d'article suggéré (hot_news vs long_form) et longueur cible pour l'IA.
     */
    private function scoreTopic(Collection $group, int $totalPoolSize): array
    {
        $weights = $this->getWeights();
        $config = $this->getClusteringConfig();
        $minItemsPerTopic = $config['min_items_per_topic'] ?? 2;
        $weightDiversity = $weights['diversity'] ?? 15;

        $items = $group->pluck('item');
        $scores = $group->pluck('score');
        $allKeywords = $group->pluck('keywords')->flatten(1);

        $avgItemScore = $scores->avg();

        $sourceIds = $items->map(fn ($item) => $item->rssFeed?->source_id)->filter()->unique();
        $sourceCount = $sourceIds->count();
        $diversityBonus = min($weightDiversity, $sourceCount * 10);

        $multiSourceBonus = $items->count() >= $minItemsPerTopic ? 15 : 0;

        // Bonus "fréquence du sujet" : si beaucoup d'articles sur ce thème (ex: 10/50), ce sujet ressort → priorité
        $topicFrequencyBonus = 0;
        $topicFreqConfig = config('selection.topic_frequency', []);
        if (! empty($topicFreqConfig['enabled']) && $totalPoolSize > 0) {
            $ratio = $group->count() / $totalPoolSize;
            $threshold = $topicFreqConfig['ratio_threshold'] ?? 0.10;
            $maxBonus = $topicFreqConfig['max_bonus'] ?? 20;
            if ($ratio >= $threshold) {
                $topicFrequencyBonus = (int) round(min($maxBonus, $ratio * $maxBonus * 2));
            }
        }

        $topicScore = (int) round($avgItemScore + $diversityBonus + $multiSourceBonus + $topicFrequencyBonus);
        $topicScore = min(100, $topicScore);

        // Extraire les mots-clés SEO consolidés (top 10)
        $consolidatedKeywords = $this->consolidateKeywords($allKeywords);

        // Déterminer le topic label
        $topicLabel = $this->generateTopicLabel($items, $consolidatedKeywords);

        // Catégorie dominante
        $categoryId = $items->pluck('category_id')->mode()[0] ?? null;
        $category = $categoryId ? Category::find($categoryId) : null;

        // Qualité moyenne des enrichissements
        $avgQuality = $items->map(fn ($i) => $i->enrichedItem?->quality_score ?? 0)->avg();

        // Type d'article suggéré (hot_news vs article de fond) pour que l'IA adapte ton et longueur
        $newestDate = $items->max(fn ($i) => $i->published_at ?? $i->fetched_at);
        $hotNewsHours = (int) (config('selection.freshness.hot_news_hours') ?? 48);
        $suggestedArticleType = ($newestDate && now()->diffInHours($newestDate) <= $hotNewsHours)
            ? 'hot_news'
            : ($items->count() >= 3 ? 'long_form' : 'standard');

        $articleTypesConfig = config('selection.article_types', []);
        $typeConfig = $articleTypesConfig[$suggestedArticleType] ?? $articleTypesConfig['standard'] ?? [];
        $suggestedMinWords = $typeConfig['min_words'] ?? 800;
        $suggestedMaxWords = $typeConfig['max_words'] ?? 1200;

        $reasoning = $this->buildReasoning(
            $items,
            $sourceCount,
            $consolidatedKeywords,
            $avgQuality,
            $avgItemScore,
            $totalPoolSize,
            $topicFrequencyBonus
        );

        $contextPriority = $totalPoolSize > 0 && $topicFrequencyBonus > 0
            ? sprintf(
                'Sur %d articles analysés, %d portent sur ce sujet (tendance, corrélation). Ce sujet est prioritaire.',
                $totalPoolSize,
                $items->count()
            )
            : null;

        return [
            'topic' => $topicLabel,
            'score' => $topicScore,
            'reasoning' => $reasoning,
            'context_priority' => $contextPriority,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ] : null,
            'seo_keywords' => array_slice($consolidatedKeywords, 0, 10),
            'items' => $items->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->url,
                'source' => $item->rssFeed?->source?->name ?? 'Inconnu',
                'quality_score' => $item->enrichedItem?->quality_score ?? 0,
                'published_at' => $item->published_at?->toIso8601String(),
            ])->values()->toArray(),
            'source_count' => $sourceCount,
            'avg_quality' => round($avgQuality, 1),
            'suggested_article_type' => $suggestedArticleType,
            'suggested_min_words' => $suggestedMinWords,
            'suggested_max_words' => $suggestedMaxWords,
        ];
    }

    /**
     * Consolide les mots-clés de plusieurs items en un top-N.
     */
    private function consolidateKeywords(Collection $allKeywords): array
    {
        $merged = [];
        foreach ($allKeywords as $kw) {
            $word = $kw['word'];
            if (! isset($merged[$word])) {
                $merged[$word] = ['word' => $word, 'frequency' => 0, 'seo_weight' => $kw['seo_weight']];
            }
            $merged[$word]['frequency'] += $kw['frequency'];
            $merged[$word]['seo_weight'] = max($merged[$word]['seo_weight'], $kw['seo_weight']);
        }

        usort($merged, fn ($a, $b) => ($b['seo_weight'] * 5 + $b['frequency']) <=> ($a['seo_weight'] * 5 + $a['frequency']));

        return array_values(array_slice($merged, 0, 10));
    }

    /**
     * Génère un label descriptif pour le topic.
     */
    private function generateTopicLabel(Collection $items, array $keywords): string
    {
        // Prendre les 3 meilleurs mots-clés pour nommer le sujet
        $topWords = array_slice(array_column($keywords, 'word'), 0, 3);

        if (empty($topWords)) {
            return Str::limit($items->first()?->title ?? 'Sujet inconnu', 80);
        }

        return implode(' / ', array_map(fn ($w) => Str::ucfirst($w), $topWords));
    }

    /**
     * Construit l'explication de POURQUOI cet article a été sélectionné (pour l'IA et l'éditeur).
     */
    private function buildReasoning(
        Collection $items,
        int $sourceCount,
        array $keywords,
        float $avgQuality,
        float $avgItemScore,
        int $totalPoolSize = 0,
        int $topicFrequencyBonus = 0
    ): string {
        $reasons = [];

        if ($totalPoolSize > 0 && $topicFrequencyBonus > 0) {
            $reasons[] = sprintf(
                'Sujet prioritaire : %d articles sur %d traitent de ce thème (tendance, corrélation) → bonus de pertinence.',
                $items->count(),
                $totalPoolSize
            );
        }

        // Nombre de sources
        if ($sourceCount >= 3) {
            $reasons[] = "Couverture multi-sources ({$sourceCount} sources différentes) = synthèse à haute valeur ajoutée";
        } elseif ($sourceCount >= 2) {
            $reasons[] = "Croisement de {$sourceCount} sources pour une perspective équilibrée";
        } else {
            $reasons[] = "Source unique mais contenu riche et détaillé";
        }

        // Qualité
        if ($avgQuality >= 70) {
            $reasons[] = sprintf("Qualité moyenne élevée (%.0f/100) : contenu bien structuré et informatif", $avgQuality);
        } elseif ($avgQuality >= 50) {
            $reasons[] = sprintf("Qualité correcte (%.0f/100)", $avgQuality);
        }

        // SEO
        $topKeywords = array_slice(array_column($keywords, 'word'), 0, 5);
        if (! empty($topKeywords)) {
            $kwList = implode(', ', $topKeywords);
            $avgSeo = collect($keywords)->take(5)->avg('seo_weight');
            if ($avgSeo >= 60) {
                $reasons[] = "Fort potentiel SEO (score moyen : " . round($avgSeo) . "/100) sur : {$kwList}";
            } else {
                $reasons[] = "Mots-clés ciblés : {$kwList}";
            }
        }

        // Fraîcheur
        $newestDate = $items->max(fn ($i) => $i->published_at ?? $i->fetched_at);
        if ($newestDate && now()->diffInHours($newestDate) <= 48) {
            $reasons[] = "Actualité très récente (< 48h)";
        } elseif ($newestDate && now()->diffInDays($newestDate) <= 3) {
            $reasons[] = "Actualité récente (< 3 jours)";
        }

        // Nombre d'items
        $count = $items->count();
        if ($count >= 3) {
            $reasons[] = "{$count} articles sources permettent une synthèse approfondie";
        }

        return implode('. ', $reasons) . '.';
    }

    /**
     * Stop words français courants.
     */
    private function getStopWords(): array
    {
        return [
            'le', 'la', 'les', 'un', 'une', 'des', 'du', 'de', 'ce', 'ces',
            'et', 'ou', 'mais', 'donc', 'car', 'ni', 'que', 'qui', 'quoi',
            'dans', 'sur', 'sous', 'avec', 'sans', 'pour', 'par', 'entre',
            'est', 'sont', 'être', 'avoir', 'fait', 'faire', 'peut', 'tout',
            'plus', 'pas', 'très', 'bien', 'aussi', 'même', 'encore', 'déjà',
            'elle', 'il', 'ils', 'nous', 'vous', 'leur', 'ses', 'son', 'leur',
            'cette', 'cet', 'quel', 'comme', 'mais', 'alors', 'dont', 'après',
        ];
    }
}
