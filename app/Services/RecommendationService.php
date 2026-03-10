<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\ReadingHistory;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * Generate personalized article recommendations.
     *
     * Algorithm weights:
     * - User interests (category match)  : 40%
     * - Reading history (avoid re-read)  : penalty
     * - Article quality                  : 25%
     * - Popularity (views proxy)         : 15%
     * - Freshness                        : 20%
     *
     * @param array $interests Category slugs
     * @param string|null $userId
     * @param string|null $sessionId
     * @param int $limit
     * @return Collection
     */
    public function recommend(
        array $interests = [],
        ?string $userId = null,
        ?string $sessionId = null,
        int $limit = 6,
        string $locale = 'fr'
    ): Collection {
        // Get articles already read to exclude or deprioritize
        $readArticleIds = collect();
        if ($userId || $sessionId) {
            $readArticleIds = ReadingHistory::forViewer($userId, $sessionId)
                ->where('progress', '>=', 50) // Consider "read" if >50%
                ->pluck('article_id');
        }

        // Resolve category IDs from interest slugs
        $interestCategoryIds = collect();
        if (! empty($interests)) {
            $interestCategoryIds = Category::whereIn('slug', $interests)->pluck('id');
        }

        $lang = in_array($locale, ['fr', 'nl'], true) ? $locale : 'fr';

        // Fetch candidate articles (filtrés par langue)
        $candidates = Article::published()
            ->forLocale($lang)
            ->with('category')
            ->whereNotIn('id', $readArticleIds)
            ->orderByDesc('published_at')
            ->limit(100) // candidate pool
            ->get();

        if ($candidates->isEmpty()) {
            return collect();
        }

        // Score each article
        $now = now();
        $maxAge = 30; // days for freshness calculation

        $scored = $candidates->map(function (Article $article) use ($interestCategoryIds, $now, $maxAge) {
            $score = 0;

            // Interest match (40%)
            if ($interestCategoryIds->isNotEmpty() && $interestCategoryIds->contains($article->category_id)) {
                $score += 40;
            } elseif ($interestCategoryIds->isEmpty()) {
                // No preferences: give a base score
                $score += 20;
            }

            // Quality (25%) — normalize 0-100 to 0-25
            $score += ($article->quality_score / 100) * 25;

            // Freshness (20%) — newer is better
            $daysOld = $article->published_at ? $now->diffInDays($article->published_at) : $maxAge;
            $freshness = max(0, 1 - ($daysOld / $maxAge));
            $score += $freshness * 20;

            // Popularity proxy (15%) — reading_time as engagement indicator
            $score += min(15, $article->reading_time * 1.5);

            return [
                'article' => $article,
                'score'   => round($score, 2),
            ];
        });

        // Sort by score descending and take limit
        return $scored->sortByDesc('score')
            ->take($limit)
            ->values()
            ->map(fn ($item) => $item['article']);
    }
}
