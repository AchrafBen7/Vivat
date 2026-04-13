<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\EnrichedItem;
use App\Models\RssItem;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TopicScorerService
{
    /**
     * @param  null|callable(string): ?Category  $categoryResolver
     */
    public function __construct(
        private readonly ?Closure $categoryResolver = null,
        private readonly ?array $weightsOverride = null,
        private readonly ?int $freshnessDecayDaysOverride = null,
        private readonly ?array $clusteringConfigOverride = null,
    ) {}

    public function scoreItem(RssItem $item): int
    {
        $weights = $this->getWeights();
        $decayDays = $this->getFreshnessDecayDays();
        $score = 0;

        $publishedAt = $item->published_at ?? $item->fetched_at ?? $item->created_at;
        if ($publishedAt) {
            $daysOld = max(0, $publishedAt->diffInDays(now(), false));
            $freshnessRatio = max(0, 1 - ($daysOld / $decayDays));
            $score += (int) round($freshnessRatio * ($weights['freshness'] ?? 25));
        }

        $enriched = $item->enrichedItem;
        if ($enriched instanceof EnrichedItem) {
            $qualityRatio = $enriched->quality_score / 100;
            $score += (int) round($qualityRatio * ($weights['quality'] ?? 25));

            if ($enriched->getWordCount() >= 1000) {
                $score += 5;
            }
        }

        $keywords = $this->extractKeywords($item);
        $seoScore = $this->estimateSeoScore($keywords, $item->category);
        $score += (int) round(($seoScore / 100) * ($weights['seo'] ?? 30));

        return min(100, $score);
    }

    /**
     * @return array<int, array{word: string, frequency: int, seo_weight: int}>
     */
    public function extractKeywords(RssItem $item): array
    {
        $enriched = $item->enrichedItem;
        if (! $enriched instanceof EnrichedItem) {
            return [];
        }

        $text = mb_strtolower(implode(' ', [
            $item->title ?? '',
            $enriched->lead ?? '',
            implode(' ', $this->normalizeTextList($enriched->key_points ?? [])),
            implode(' ', $this->normalizeTextList($enriched->headings ?? [])),
        ]));

        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $words = preg_split('/\s+/', (string) $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = array_values(array_filter(array_map(
            fn (mixed $word): ?string => is_string($word) ? $this->cleanKeywordToken($word) : null,
            $words ?: []
        )));

        $stopWords = $this->getStopWords();
        $words = array_filter($words, fn ($word) => mb_strlen($word) >= 4 && ! in_array($word, $stopWords, true));

        $frequencies = array_count_values($words);
        arsort($frequencies);

        $keywords = [];
        foreach (array_slice($frequencies, 0, 15, true) as $word => $count) {
            $keywords[] = [
                'word' => $word,
                'frequency' => $count,
                'seo_weight' => $this->estimateKeywordSeoWeight($word),
            ];
        }

        usort($keywords, fn ($a, $b) => ($b['seo_weight'] * 10 + $b['frequency']) <=> ($a['seo_weight'] * 10 + $a['frequency']));

        return $keywords;
    }

    /**
     * @param  Collection<int, array{item: RssItem, score: int, keywords: array}>  $scoredItems
     * @return array<int, Collection<int, array{item: RssItem, score: int, keywords: array}>>
     */
    public function clusterByTopic(Collection $scoredItems): array
    {
        $groups = [];
        $assigned = [];
        $itemsArray = $scoredItems->values()->all();
        $config = $this->getClusteringConfig();
        $threshold = $config['similarity_threshold'] ?? 0.20;
        $maxPerTopic = $config['max_items_per_topic'] ?? 5;

        foreach ($itemsArray as $i => $entry) {
            if (in_array($i, $assigned, true)) {
                continue;
            }

            $group = collect([$entry]);
            $assigned[] = $i;
            $keywordsA = collect($entry['keywords'])->pluck('word')->toArray();

            foreach ($itemsArray as $j => $other) {
                if ($i === $j || in_array($j, $assigned, true)) {
                    continue;
                }

                $keywordsB = collect($other['keywords'])->pluck('word')->toArray();
                $similarity = $this->keywordSimilarity($keywordsA, $keywordsB);

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

    public function inferCategoryFromKeywords(array $keywords): ?Category
    {
        $categoryKeywords = [
            'energie' => ['énergie', 'renouvelable', 'nucléaire', 'pétrole', 'électricité', 'solaire', 'éolien', 'photovoltaïque', 'hydrogène', 'biomasse', 'carburant', 'gaz', 'charbon', 'réseau', 'watt'],
            'au-quotidien' => ['quotidien', 'habitude', 'consommation', 'recyclage', 'déchet', 'compostage', 'alimentation', 'bio', 'courses', 'supermarché', 'emballage', 'zéro', 'plastique', 'eau'],
            'finance' => ['finance', 'investissement', 'banque', 'épargne', 'bourse', 'économie', 'budget', 'impôt', 'fiscal', 'crédit', 'assurance', 'pension', 'retraite', 'subvention'],
            'technologie' => ['technologie', 'numérique', 'intelligence', 'artificielle', 'application', 'logiciel', 'données', 'startup', 'innovation', 'robot', 'cyber', 'blockchain', 'algorithme'],
            'chez-soi' => ['maison', 'rénovation', 'isolation', 'thermique', 'chauffage', 'habitat', 'logement', 'construction', 'immobilier', 'jardin', 'décoration', 'meubles', 'bricolage'],
            'mode' => ['mode', 'textile', 'vêtement', 'fast', 'fashion', 'marque', 'collection', 'coton', 'tissu', 'seconde', 'main', 'friperie', 'tendance'],
            'sante' => ['santé', 'médecin', 'hôpital', 'maladie', 'traitement', 'vaccin', 'pollution', 'pesticides', 'cancer', 'bien-être', 'mental', 'sport', 'sommeil', 'stress'],
            'voyage' => ['voyage', 'tourisme', 'avion', 'train', 'mobilité', 'transport', 'vélo', 'voiture', 'électrique', 'destination', 'vacances', 'compagnie', 'aérien'],
            'famille' => ['famille', 'enfant', 'éducation', 'école', 'parent', 'naissance', 'grossesse', 'adolescent', 'jeunesse', 'génération', 'héritage'],
        ];

        $words = collect($keywords)->pluck('word')->map(fn ($word) => mb_strtolower((string) $word))->toArray();
        $bestSlug = null;
        $bestScore = 0.0;

        foreach ($categoryKeywords as $slug => $categoryWords) {
            $matches = count(array_intersect($words, $categoryWords));

            foreach ($words as $word) {
                foreach ($categoryWords as $categoryWord) {
                    if ($word !== $categoryWord && (str_contains($word, $categoryWord) || str_contains($categoryWord, $word))) {
                        $matches += 0.5;
                    }
                }
            }

            if ($matches > $bestScore) {
                $bestScore = $matches;
                $bestSlug = $slug;
            }
        }

        if (! $bestSlug || $bestScore < 1) {
            return null;
        }

        if ($this->categoryResolver instanceof Closure) {
            return ($this->categoryResolver)($bestSlug);
        }

        return Category::where('slug', $bestSlug)->first();
    }

    public function resolveCategoryForItems(Collection $items, array $keywords = []): ?Category
    {
        $candidates = collect();

        $directCategories = $items->map(fn (RssItem $item) => $item->category)
            ->filter(fn ($category) => $category instanceof Category);
        $candidates = $candidates->merge($directCategories);

        $clusterInferred = $this->inferCategoryFromKeywords($keywords);
        if ($clusterInferred) {
            $candidates->push($clusterInferred);
        }

        foreach ($items as $item) {
            if (! $item instanceof RssItem) {
                continue;
            }

            $inferred = $this->inferCategoryFromKeywords($this->extractKeywords($item));
            if ($inferred) {
                $candidates->push($inferred);
            }
        }

        $candidates = $candidates
            ->filter(fn ($category) => $category instanceof Category)
            ->unique(fn (Category $category) => $category->id)
            ->values();

        if ($candidates->isEmpty()) {
            $candidates = Schema::hasTable('categories')
                ? Category::query()->orderedForHome()->get()
                : collect();
        }

        if ($candidates->isEmpty()) {
            return null;
        }

        $best = $candidates
            ->map(function (Category $category) use ($items, $keywords, $clusterInferred): array {
                $directMatches = $items->filter(fn (RssItem $item) => $item->category_id === $category->id)->count();
                $alignment = $this->estimateCategoryAlignment($items, $category, $keywords);
                $recentCount = Schema::hasTable('articles')
                    ? Article::query()
                        ->where('category_id', $category->id)
                        ->whereIn('status', ['draft', 'published'])
                        ->where('created_at', '>=', now()->subHours(72))
                        ->count()
                    : 0;

                $score = ($directMatches * 4)
                    + ($alignment * 10)
                    + ($clusterInferred?->id === $category->id ? 3 : 0)
                    - min(4, $recentCount) * 1.5;

                return [
                    'category' => $category,
                    'score' => $score,
                    'direct_matches' => $directMatches,
                    'alignment' => $alignment,
                    'recent_count' => $recentCount,
                ];
            })
            ->sort(function (array $left, array $right): int {
                if ($left['score'] === $right['score']) {
                    return $left['recent_count'] <=> $right['recent_count'];
                }

                return $right['score'] <=> $left['score'];
            })
            ->first();

        return $best['category'] ?? null;
    }

    /**
     * @param  mixed  $items
     * @return array<int, string>
     */
    public function normalizeTextList(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (is_string($item) || is_numeric($item)) {
                $value = trim((string) $item);
                if ($value !== '') {
                    $normalized[] = $value;
                }

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            foreach (['text', 'title', 'heading', 'label', 'value', 'name'] as $key) {
                $candidate = $item[$key] ?? null;

                if (is_string($candidate) || is_numeric($candidate)) {
                    $value = trim((string) $candidate);
                    if ($value !== '') {
                        $normalized[] = $value;
                    }
                }
            }
        }

        return $normalized;
    }

    public function cleanKeywordToken(string $word): ?string
    {
        $word = mb_strtolower(trim($word));
        $word = preg_replace("/^[^\\p{L}\\p{N}]+|[^\\p{L}\\p{N}]+$/u", '', $word) ?? '';
        $word = preg_replace("/['`]+/u", '', $word) ?? $word;
        $word = preg_replace('/-{2,}/', '-', $word) ?? $word;

        if ($word === '') {
            return null;
        }

        if (! preg_match('/^[\p{L}\p{N}-]+$/u', $word)) {
            return null;
        }

        $length = mb_strlen($word);
        if ($length < 4 || $length > 30) {
            return null;
        }

        if ($this->isSuspiciousKeyword($word)) {
            return null;
        }

        return $word;
    }

    /**
     * @param  array<int, array{word: string, frequency: int, seo_weight: int}>  $keywords
     */
    public function estimateSeoScore(array $keywords, ?Category $category): int
    {
        if (empty($keywords)) {
            return 0;
        }

        $totalWeight = 0;
        $count = 0;
        foreach (array_slice($keywords, 0, 5) as $keyword) {
            $totalWeight += $keyword['seo_weight'];
            $count++;
        }

        $avgWeight = $count > 0 ? $totalWeight / $count : 0;

        return (int) round($avgWeight);
    }

    public function estimateCategoryAlignment(Collection $items, Category $category, array $keywords): float
    {
        if (Schema::hasTable('sub_categories')) {
            $category->loadMissing('subCategories');
        } else {
            $category->setRelation('subCategories', collect());
        }

        $terms = collect([
            $category->name,
            $category->slug,
            $category->description,
            ...$category->subCategories->pluck('name')->all(),
            ...$category->subCategories->pluck('slug')->all(),
        ])->filter()
            ->map(fn (string $term): string => Str::of($term)->lower()->ascii()->replace('-', ' ')->value())
            ->unique()
            ->values();

        if ($terms->isEmpty()) {
            return 0.0;
        }

        $text = Str::of(implode(' ', array_filter([
            $items->pluck('title')->implode(' '),
            $items->map(fn (RssItem $item) => $item->enrichedItem?->lead ?? '')->implode(' '),
            implode(' ', array_column($keywords, 'word')),
        ])))->lower()->ascii()->replace('-', ' ')->value();

        $matches = 0.0;
        foreach ($terms as $term) {
            if ($term === '') {
                continue;
            }

            if (str_contains($text, $term)) {
                $matches += 1;
                continue;
            }

            foreach (explode(' ', $term) as $part) {
                if ($part !== '' && str_contains($text, $part)) {
                    $matches += 0.35;
                }
            }
        }

        return min(1.0, $matches / max(1, $terms->count() * 0.75));
    }

    public function keywordSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        $intersection = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));

        return $union > 0 ? $intersection / $union : 0.0;
    }

    public function estimateKeywordSeoWeight(string $word): int
    {
        $weight = 50;
        $length = mb_strlen($word);

        if ($length >= 6 && $length <= 12) {
            $weight += 15;
        } elseif ($length >= 13) {
            $weight += 20;
        } elseif ($length <= 4) {
            $weight -= 10;
        }

        $highValueTerms = [
            'transition', 'écologique', 'renouvelable', 'biodiversité', 'carbone',
            'durable', 'énergie', 'recyclage', 'pollution', 'climat',
            'empreinte', 'neutralité', 'sobriété', 'permaculture', 'agroécologie',
            'rénovation', 'thermique', 'photovoltaïque', 'hydrogène', 'biomasse',
            'pesticides', 'déforestation', 'compostage', 'mobilité', 'véhicule',
            'électrique', 'consommation', 'responsable', 'zéro déchet',
        ];

        if (in_array($word, $highValueTerms, true)) {
            $weight += 25;
        }

        $genericTerms = [
            'article', 'france', 'monde', 'année', 'aussi',
            'plus', 'faire', 'avoir', 'être', 'très', 'tout',
            'comme', 'avec', 'pour', 'dans', 'cette', 'sont',
        ];

        if (in_array($word, $genericTerms, true)) {
            $weight -= 30;
        }

        return max(0, min(100, $weight));
    }

    public function isSuspiciousKeyword(string $word): bool
    {
        if (preg_match('/(.)\1{2,}/u', $word)) {
            return true;
        }

        if (preg_match('/[bcdfghjklmnpqrstvwxyz]{5,}/iu', $word)) {
            return true;
        }

        $unlikelyClusters = ['bj', 'cj', 'fj', 'gq', 'jq', 'jv', 'qj', 'qx', 'vbx', 'wx', 'xq', 'zq'];
        foreach ($unlikelyClusters as $cluster) {
            if (str_contains(mb_strtolower($word), $cluster)) {
                return true;
            }
        }

        $lettersOnly = preg_replace('/[^a-zàâäéèêëîïôöùûüÿç]/iu', '', $word) ?? '';
        $length = mb_strlen($lettersOnly);

        if ($length >= 6) {
            preg_match_all('/[aeiouyàâäéèêëîïôöùûüÿ]/iu', $lettersOnly, $matches);
            $vowelCount = count($matches[0] ?? []);

            if ($vowelCount > 0 && ($vowelCount / max($length, 1)) < 0.22) {
                return true;
            }
        }

        return false;
    }

    private function getWeights(): array
    {
        if (is_array($this->weightsOverride)) {
            return array_merge([
                'freshness' => 25,
                'quality' => 25,
                'seo' => 30,
                'diversity' => 15,
                'topic_frequency' => 5,
            ], $this->weightsOverride);
        }

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
        if ($this->freshnessDecayDaysOverride !== null) {
            return $this->freshnessDecayDaysOverride;
        }

        return (int) (config('selection.freshness.decay_days') ?? 7);
    }

    private function getClusteringConfig(): array
    {
        if (is_array($this->clusteringConfigOverride)) {
            return array_merge([
                'min_items_per_topic' => 1,
                'max_items_per_topic' => 6,
                'similarity_threshold' => 0.12,
            ], $this->clusteringConfigOverride);
        }

        return array_merge([
            'min_items_per_topic' => 1,
            'max_items_per_topic' => 6,
            'similarity_threshold' => 0.12,
        ], config('selection.clustering', []));
    }

    /**
     * @return array<int, string>
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
