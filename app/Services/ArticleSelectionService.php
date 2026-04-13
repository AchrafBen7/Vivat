<?php

namespace App\Services;

use App\Models\Article;
use App\Models\RssItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Service de sélection intelligente des articles à générer.
 *
 * Orchestrateur :
 *  1. Récupère les items enrichis disponibles
 *  2. Écarte les sujets hors scope
 *  3. Délègue scoring et clustering au TopicScorerService
 *  4. Score les clusters et construit les propositions finales
 */
class ArticleSelectionService
{
    public function __construct(
        private readonly TopicScorerService $topicScorer,
    ) {}

    public function selectBestTopics(int $count = 1, ?string $categoryId = null): array
    {
        $query = RssItem::query()
            ->where('status', 'enriched')
            ->whereDoesntHave('articleSources', function ($query): void {
                $query->whereHas('article', fn ($articleQuery) => $articleQuery->whereIn('status', ['draft', 'review', 'published']));
            })
            ->whereHas('enrichedItem')
            ->with(['enrichedItem', 'rssFeed.source', 'category']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            return [];
        }

        $items = $items->filter(fn (RssItem $item): bool => ! $this->isOffTopic($item));

        if ($items->isEmpty()) {
            return [];
        }

        $scoredItems = $items->map(fn (RssItem $item): array => [
            'item' => $item,
            'score' => $this->topicScorer->scoreItem($item),
            'keywords' => $this->topicScorer->extractKeywords($item),
        ]);

        $topics = $this->topicScorer->clusterByTopic($scoredItems);
        $totalPoolSize = $items->count();
        $minItems = $this->getClusteringConfig()['min_items_per_topic'];

        // Compte d'articles récents (48h) par catégorie pour la diversité
        $recentCountByCategory = Article::whereIn('status', ['draft', 'published'])
            ->where('created_at', '>=', now()->subHours(48))
            ->whereNotNull('category_id')
            ->selectRaw('category_id, count(*) as cnt')
            ->groupBy('category_id')
            ->pluck('cnt', 'category_id')
            ->all();

        $maxRecentCount = ! empty($recentCountByCategory) ? max($recentCountByCategory) : 0;

        $scoredTopics = collect($topics)
            ->filter(function (Collection $group) use ($minItems, $recentCountByCategory, $maxRecentCount): bool {
                // Accepter 1 seul item pour les catégories peu représentées vs la catégorie dominante
                $categoryId = collect($group->pluck('item')->pluck('category_id')->filter())->mode()[0] ?? null;
                $categoryRecentCount = $categoryId ? ($recentCountByCategory[$categoryId] ?? 0) : $minItems;
                $effectiveMin = ($maxRecentCount > 0 && $categoryRecentCount < ($maxRecentCount / 2)) ? 1 : $minItems;
                return $group->count() >= $effectiveMin;
            })
            ->map(fn (Collection $group): array => $this->scoreTopic($group, $totalPoolSize));

        if ($scoredTopics->isEmpty() && $scoredItems->isNotEmpty()) {
            $bestSingleItem = $scoredItems
                ->sortByDesc('score')
                ->values()
                ->first();

            if (is_array($bestSingleItem) && $this->canUseSingleSourceFallback($bestSingleItem)) {
                $fallbackTopic = $this->scoreTopic(collect([$bestSingleItem]), $totalPoolSize);
                $fallbackTopic['reasoning'] .= ' Fallback activé : meilleur sujet disponible retenu malgré une seule source enrichie.';
                $fallbackTopic['selection_mode'] = 'single_source_fallback';
                $scoredTopics = collect([$fallbackTopic]);
            }
        }

        return $scoredTopics
            ->sortByDesc('score')
            ->take($count)
            ->values()
            ->toArray();
    }

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

    private function getClusteringConfig(): array
    {
        return array_merge([
            'min_items_per_topic' => 2,
            'max_items_per_topic' => 6,
            'similarity_threshold' => 0.12,
        ], config('selection.clustering', []));
    }

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

        $topicFrequencyBonus = 0;
        $topicFrequencyConfig = config('selection.topic_frequency', []);
        if (! empty($topicFrequencyConfig['enabled']) && $totalPoolSize > 0) {
            $ratio = $group->count() / $totalPoolSize;
            $threshold = $topicFrequencyConfig['ratio_threshold'] ?? 0.10;
            $maxBonus = $topicFrequencyConfig['max_bonus'] ?? 20;

            if ($ratio >= $threshold) {
                $topicFrequencyBonus = (int) round(min($maxBonus, $ratio * $maxBonus * 2));
            }
        }

        $topicScore = (int) round($avgItemScore + $diversityBonus + $multiSourceBonus + $topicFrequencyBonus);
        $topicScore = min(100, $topicScore);

        $consolidatedKeywords = $this->consolidateKeywords($allKeywords);
        $topicLabel = $this->generateTopicLabel($items, $consolidatedKeywords);

        $category = $this->topicScorer->resolveCategoryForItems($items, $consolidatedKeywords);

        $categoryAlignment = $category ? $this->topicScorer->estimateCategoryAlignment($items, $category, $consolidatedKeywords) : 0.0;
        $editorialConfig = config('selection.editorial', []);
        $alignmentBonus = match (true) {
            $categoryAlignment >= 0.72 => (int) ($editorialConfig['alignment_bonus_strong'] ?? 12),
            $categoryAlignment >= 0.50 => (int) ($editorialConfig['alignment_bonus_medium'] ?? 6),
            $categoryAlignment > 0 && $categoryAlignment < 0.22 => -1 * (int) ($editorialConfig['alignment_penalty_low'] ?? 12),
            default => 0,
        };

        $weakTopicPenalty = $this->isWeakEditorialTopic($items, $consolidatedKeywords)
            ? (int) ($editorialConfig['weak_topic_penalty'] ?? 18)
            : 0;

        $avgQuality = $items->map(fn ($item) => $item->enrichedItem?->quality_score ?? 0)->avg();
        $newestDate = $items->max(fn ($item) => $item->published_at ?? $item->fetched_at);
        $hotNewsHours = (int) (config('selection.freshness.hot_news_hours') ?? 48);
        $suggestedArticleType = ($newestDate && now()->diffInHours($newestDate) <= $hotNewsHours)
            ? 'hot_news'
            : ($items->count() >= 3 ? 'long_form' : 'standard');

        $articleTypesConfig = config('selection.article_types', []);
        $typeConfig = $articleTypesConfig[$suggestedArticleType] ?? $articleTypesConfig['standard'] ?? [];

        // Pénalité si cette catégorie a déjà un brouillon récent (diversité éditoriale)
        $recentCategoryPenalty = 0;
        if ($category) {
            $recentCount = Article::where('category_id', $category->id)
                ->whereIn('status', ['draft', 'published'])
                ->where('created_at', '>=', now()->subHours(48))
                ->count();
            if ($recentCount >= 3) {
                $recentCategoryPenalty = 30;
            } elseif ($recentCount >= 2) {
                $recentCategoryPenalty = 20;
            } elseif ($recentCount >= 1) {
                $recentCategoryPenalty = 10;
            }
        }

        $topicScore = (int) round($avgItemScore + $diversityBonus + $multiSourceBonus + $topicFrequencyBonus + $alignmentBonus - $weakTopicPenalty - $recentCategoryPenalty);
        $topicScore = min(100, max(0, $topicScore));

        $reasoning = $this->buildReasoning(
            $items,
            $sourceCount,
            $consolidatedKeywords,
            $avgQuality,
            $avgItemScore,
            $totalPoolSize,
            $topicFrequencyBonus,
            $categoryAlignment,
            $weakTopicPenalty > 0,
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
            'category_alignment' => round($categoryAlignment, 2),
            'suggested_article_type' => $suggestedArticleType,
            'suggested_min_words' => $typeConfig['min_words'] ?? 800,
            'suggested_max_words' => $typeConfig['max_words'] ?? 1200,
        ];
    }

    private function consolidateKeywords(Collection $allKeywords): array
    {
        $merged = [];

        foreach ($allKeywords as $keyword) {
            $word = $this->topicScorer->cleanKeywordToken((string) ($keyword['word'] ?? ''));
            if ($word === null) {
                continue;
            }

            if (! isset($merged[$word])) {
                $merged[$word] = [
                    'word' => $word,
                    'frequency' => 0,
                    'seo_weight' => (int) ($keyword['seo_weight'] ?? 0),
                ];
            }

            $merged[$word]['frequency'] += (int) ($keyword['frequency'] ?? 0);
            $merged[$word]['seo_weight'] = max($merged[$word]['seo_weight'], (int) ($keyword['seo_weight'] ?? 0));
        }

        $merged = array_values(array_filter($merged, function (array $keyword): bool {
            $word = $keyword['word'] ?? '';
            $frequency = (int) ($keyword['frequency'] ?? 0);
            $seoWeight = (int) ($keyword['seo_weight'] ?? 0);

            if ($word === '' || $this->topicScorer->isSuspiciousKeyword($word)) {
                return false;
            }

            return $frequency >= 2 || $seoWeight >= 55;
        }));

        usort($merged, fn ($a, $b) => ($b['seo_weight'] * 5 + $b['frequency']) <=> ($a['seo_weight'] * 5 + $a['frequency']));

        return array_values(array_slice($merged, 0, 10));
    }

    private function generateTopicLabel(Collection $items, array $keywords): string
    {
        $topWords = array_slice(array_column($keywords, 'word'), 0, 3);

        if (empty($topWords)) {
            return Str::limit($items->first()?->title ?? 'Sujet inconnu', 80);
        }

        return implode(' / ', array_map(fn (string $word): string => Str::ucfirst($word), $topWords));
    }

    private function buildReasoning(
        Collection $items,
        int $sourceCount,
        array $keywords,
        float $avgQuality,
        float $avgItemScore,
        int $totalPoolSize = 0,
        int $topicFrequencyBonus = 0,
        float $categoryAlignment = 0.0,
        bool $isWeakTopic = false,
    ): string {
        $reasons = [];

        if ($totalPoolSize > 0 && $topicFrequencyBonus > 0) {
            $reasons[] = sprintf(
                'Sujet prioritaire : %d articles sur %d traitent de ce thème (tendance, corrélation) → bonus de pertinence.',
                $items->count(),
                $totalPoolSize
            );
        }

        if ($sourceCount >= 3) {
            $reasons[] = "Couverture multi-sources ({$sourceCount} sources différentes) = synthèse à haute valeur ajoutée";
        } elseif ($sourceCount >= 2) {
            $reasons[] = "Croisement de {$sourceCount} sources pour une perspective équilibrée";
        } else {
            $reasons[] = 'Source unique mais contenu riche et détaillé';
        }

        if ($avgQuality >= 70) {
            $reasons[] = sprintf('Qualité moyenne élevée (%.0f/100) : contenu bien structuré et informatif', $avgQuality);
        } elseif ($avgQuality >= 50) {
            $reasons[] = sprintf('Qualité correcte (%.0f/100)', $avgQuality);
        }

        if ($categoryAlignment >= 0.72) {
            $reasons[] = 'Très bon alignement avec la catégorie éditoriale et ses sous-thèmes';
        } elseif ($categoryAlignment >= 0.50) {
            $reasons[] = 'Sujet cohérent avec la catégorie éditoriale ciblée';
        } elseif ($categoryAlignment > 0 && $categoryAlignment < 0.22) {
            $reasons[] = 'Sujet faiblement aligné avec les catégories éditoriales';
        }

        $topKeywords = array_slice(array_column($keywords, 'word'), 0, 5);
        if (! empty($topKeywords)) {
            $keywordList = implode(', ', $topKeywords);
            $avgSeo = collect($keywords)->take(5)->avg('seo_weight');

            if ($avgSeo >= 60) {
                $reasons[] = 'Fort potentiel SEO (score moyen : ' . round($avgSeo) . "/100) sur : {$keywordList}";
            } else {
                $reasons[] = "Mots-clés ciblés : {$keywordList}";
            }
        }

        $newestDate = $items->max(fn ($item) => $item->published_at ?? $item->fetched_at);
        if ($newestDate && now()->diffInHours($newestDate) <= 48) {
            $reasons[] = 'Actualité très récente (< 48h)';
        } elseif ($newestDate && now()->diffInDays($newestDate) <= 3) {
            $reasons[] = 'Actualité récente (< 3 jours)';
        }

        if ($items->count() >= 3) {
            $reasons[] = $items->count() . ' articles sources permettent une synthèse approfondie';
        }

        if ($isWeakTopic) {
            $reasons[] = 'Sujet affaibli car trop anecdotique ou trop étroit pour devenir une vraie priorité éditoriale';
        }

        return implode('. ', $reasons) . '.';
    }

    private function isWeakEditorialTopic(Collection $items, array $keywords): bool
    {
        $editorialConfig = config('selection.editorial', []);
        $weakTerms = array_map('mb_strtolower', $editorialConfig['weak_topic_terms'] ?? []);
        $impactTerms = array_map('mb_strtolower', $editorialConfig['impact_terms'] ?? []);

        $text = mb_strtolower(implode(' ', array_filter([
            $items->pluck('title')->implode(' '),
            $items->map(fn (RssItem $item) => $item->enrichedItem?->lead ?? '')->implode(' '),
            implode(' ', array_column($keywords, 'word')),
        ])));

        $hasWeakSignal = false;
        foreach ($weakTerms as $term) {
            if ($term !== '' && str_contains($text, $term)) {
                $hasWeakSignal = true;
                break;
            }
        }

        if (! $hasWeakSignal) {
            return false;
        }

        foreach ($impactTerms as $term) {
            if ($term !== '' && str_contains($text, $term)) {
                return false;
            }
        }

        return true;
    }

    private function canUseSingleSourceFallback(array $bestSingleItem): bool
    {
        $item = $bestSingleItem['item'] ?? null;

        if (! $item instanceof RssItem) {
            return false;
        }

        $score = (int) ($bestSingleItem['score'] ?? 0);
        $keywords = is_array($bestSingleItem['keywords'] ?? null) ? $bestSingleItem['keywords'] : [];
        $editorialConfig = config('selection.editorial', []);
        $minScore = (int) ($editorialConfig['single_source_fallback_min_score'] ?? 70);
        $minAlignment = (float) ($editorialConfig['single_source_min_alignment'] ?? 0.35);

        if ($score < $minScore) {
            return false;
        }

        $category = $this->topicScorer->resolveCategoryForItems(collect([$item]), $keywords);
        $alignment = $category ? $this->topicScorer->estimateCategoryAlignment(collect([$item]), $category, $keywords) : 0.0;

        if ($alignment < $minAlignment) {
            return false;
        }

        return ! $this->isWeakEditorialTopic(collect([$item]), $keywords);
    }

    private function isOffTopic(RssItem $item): bool
    {
        $text = mb_strtolower(implode(' ', array_filter([
            $item->title ?? '',
            $item->enrichedItem?->lead ?? '',
            implode(' ', $item->enrichedItem?->key_points ?? []),
        ])));

        $blacklist = [
            'élection', 'elections', 'municipales', 'législatives', 'présidentielle',
            'parti politique', 'extrême droite', 'extrême gauche', 'député', 'sénateur',
            'assemblée nationale', 'sénat', 'premier ministre', 'président de la république',
            'macron', 'mélenchon', 'le pen', 'bardella', 'opposition', 'majorité parlementaire',
            'vote de confiance', 'motion de censure', 'référendum', 'campagne électorale',
            'souveraineté', 'géopolitique', 'diplomatie',
            'guerre', 'conflit armé', 'bombardement', 'missile', 'militaire', 'armée',
            'invasion', 'otan', 'cessez-le-feu', 'front', 'combattants',
            'meurtre', 'assassinat', 'viol', 'agression', 'terrorisme', 'attentat',
            'fusillade', 'enlèvement', 'homicide', 'procès criminel',
            'expulsions', 'expulsés', 'répression',
            'laïcité', 'islamophobie', 'radicalisation', 'blasphème',
            'célébrité', 'scandale', 'polémique', 'clash', 'buzz',
            'capitaliste', 'anticapitaliste', 'néolibéral', 'colonialisme',
            'libérons', 'manifeste', 'lutte', 'résistance', 'militant',
            'tribune', 'posséder', 'piéger', 'condamné', 'amende',
            'lobbies', 'lobby', 'victoire des', 'désinformation', 'propagande',
            'dénonce', 'blanchit', 'scandale',
            'chasseurs', 'chasse', 'corrida', 'abattoir',
            'procès', 'tribunal', 'condamnation', 'plainte', 'poursuite',
        ];

        foreach ($blacklist as $term) {
            if (str_contains($text, $term)) {
                return true;
            }
        }

        return false;
    }
}
