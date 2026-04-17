<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PublicPageDataService
{
    public function __construct(
        private readonly PublicCacheService $cache,
    ) {}

    public function getArticlesIndexData(string $locale = 'fr'): array
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $ttl = $this->ttl();

        $payload = $this->cache->remember('articles-index', $locale, 'page:' . $page, $ttl, function () use ($locale, $page, $perPage): array {
            $query = Article::published()
                ->forLocale($locale)
                ->with('category')
                ->orderByDesc('published_at');

            $total = (clone $query)->count();
            $articles = (clone $query)
                ->forPage($page, $perPage)
                ->get()
                ->map(fn (Article $article): array => $this->articleToArray($article))
                ->all();

            return [
                'articles' => $articles,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
            ];
        });

        return [
            'articles' => $payload['articles'],
            'pagination' => $this->makePaginator($payload['articles'], $payload['total'], $payload['per_page'], $payload['page']),
        ];
    }

    public function getSearchData(string $locale = 'fr', string $q = ''): array
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $normalized = strtolower(trim($q));

        if ($normalized === '') {
            return [
                'articles' => [],
                'pagination' => $this->makePaginator([], 0, 10, 1),
                'search_term' => $q,
                'matched_category' => null,
                'search_mosaic_padded' => false,
                'continue_reading_articles' => [],
            ];
        }

        $ttl = $this->ttl();
        $signature = 'q:' . md5($normalized) . ':page:' . $page;

        $payload = $this->cache->remember('search', $locale, $signature, $ttl, function () use ($locale, $q, $normalized, $page): array {
            $perPage = 9;
            $query = Article::published()
                ->forLocale($locale)
                ->with('category');

            $matchedCategory = Category::where('name', $normalized)
                ->orWhere('slug', $normalized)
                ->first();

            if ($matchedCategory) {
                $query->where('category_id', $matchedCategory->id);
            } else {
                if (DB::getDriverName() === 'mysql' && strlen($q) >= 2) {
                    $query->where(function ($builder) use ($q) {
                        $builder->whereFullText(['title', 'excerpt'], $q)
                            ->orWhere('meta_description', 'LIKE', '%' . addcslashes($q, '%_\\') . '%');
                    });
                } else {
                    $query->where(function ($builder) use ($q) {
                        $escaped = addcslashes($q, '%_\\');
                        $builder->where('title', 'LIKE', "%{$escaped}%")
                            ->orWhere('excerpt', 'LIKE', "%{$escaped}%")
                            ->orWhere('meta_description', 'LIKE', "%{$escaped}%");
                    });
                }
            }

            $total = (clone $query)->count();
            $pageHits = (clone $query)
                ->orderByDesc('published_at')
                ->forPage($page, $perPage)
                ->get()
                ->map(fn (Article $article): array => $this->articleToArray($article))
                ->values()
                ->all();

            $mosaicTarget = 9;
            $mosaicArticles = $this->padSearchMosaicArticles($locale, $pageHits, $mosaicTarget);
            $mosaicPadded = count($pageHits) > 0 && count($mosaicArticles) > count($pageHits);

            $excludeIds = collect($mosaicArticles)->pluck('id')->filter()->unique()->values()->all();

            $continueReadingArticles = Article::published()
                ->forLocale($locale)
                ->with('category')
                ->when($excludeIds !== [], fn ($builder) => $builder->whereNotIn('id', $excludeIds))
                ->orderByDesc('published_at')
                ->limit(20)
                ->get()
                ->map(fn (Article $article): array => $this->articleToArray($article))
                ->values()
                ->all();

            return [
                'articles' => $mosaicArticles,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'matched_category' => $matchedCategory ? $this->categoryToArray($matchedCategory, $locale) : null,
                'search_mosaic_padded' => $mosaicPadded,
                'continue_reading_articles' => $continueReadingArticles,
            ];
        });

        return [
            'articles' => $payload['articles'],
            'pagination' => $this->makePaginator($payload['articles'], $payload['total'], $payload['per_page'], $payload['page']),
            'search_term' => $q,
            'matched_category' => $payload['matched_category'],
            'search_mosaic_padded' => $payload['search_mosaic_padded'],
            'continue_reading_articles' => $payload['continue_reading_articles'],
        ];
    }

    public function getHomeData(string $locale = 'fr'): array
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $ttl = $this->ttl();
        $latestLimit = (int) config('vivat.home_latest_count', 12);

        $highlightPayload = $this->cache->remember('home-highlight', $locale, 'main', $ttl, function () use ($locale): array {
            $highlightSize = 5;

            $hotNewsForHighlight = Article::published()
                ->forLocale($locale)
                ->where('article_type', 'hot_news')
                ->with('category')
                ->orderByDesc('published_at')
                ->limit($highlightSize)
                ->get();

            $highlightIds = $hotNewsForHighlight->pluck('id')->all();
            $remaining = $highlightSize - count($highlightIds);

            $highlight = $hotNewsForHighlight;
            if ($remaining > 0) {
                $featuredFill = Article::published()
                    ->forLocale($locale)
                    ->with('category')
                    ->whereNotIn('id', $highlightIds)
                    ->where(fn ($query) => $query->where('article_type', 'hot_news')->orWhereNotNull('cover_image_url'))
                    ->orderByDesc('published_at')
                    ->limit($remaining)
                    ->get();

                $highlight = $highlight->merge($featuredFill)->take($highlightSize);
                $highlightIds = $highlight->pluck('id')->all();
                $remaining = $highlightSize - $highlight->count();
            }

            if ($remaining > 0) {
                $fallbackFill = Article::published()
                    ->forLocale($locale)
                    ->with('category')
                    ->whereNotIn('id', $highlightIds)
                    ->orderByDesc('published_at')
                    ->limit($remaining)
                    ->get();

                $highlight = $highlight->merge($fallbackFill)->take($highlightSize);
            }

            $highlight = $highlight->unique('id')->values()->take($highlightSize);
            $highlightItems = $highlight->values()->all();

            if (count($highlightItems) > 0 && empty($highlightItems[0]->cover_image_url)) {
                for ($i = 1; $i < count($highlightItems); $i++) {
                    if (! empty($highlightItems[$i]->cover_image_url)) {
                        $swap = $highlightItems[0];
                        $highlightItems[0] = $highlightItems[$i];
                        $highlightItems[$i] = $swap;
                        break;
                    }
                }

                $highlight = collect($highlightItems);
            }

            return [
                'ids' => $highlight->pluck('id')->unique()->values()->all(),
                'items' => $highlight->map(fn (Article $article): array => $this->articleToArray($article))->values()->all(),
                'top_news' => $highlight->first() ? $this->articleToArray($highlight->first()) : null,
            ];
        });

        $categoriesPayload = $this->cache->remember('home-categories', $locale, 'main', $ttl, function () use ($locale): array {
            $categoriesLimit = (int) config('vivat.home_categories_count', 9);

            return Category::query()
                ->withCount(['articles as published_articles_count' => fn ($query) => $query->where('status', 'published')->where('language', $locale)])
                ->orderedForHome()
                ->limit($categoriesLimit)
                ->get()
                ->map(fn (Category $category): array => $this->categoryToArray($category, $locale))
                ->all();
        });

        $latestPayload = $this->cache->remember('home-latest', $locale, 'page:' . $page, $ttl, function () use ($locale, $page, $latestLimit, $highlightPayload): array {
            $query = Article::published()
                ->forLocale($locale)
                ->with('category')
                ->whereNotIn('id', $highlightPayload['ids'])
                ->orderByDesc('published_at');

            $total = (clone $query)->count();
            $latest = (clone $query)
                ->forPage($page, $latestLimit)
                ->get()
                ->map(fn (Article $article): array => $this->articleToArray($article))
                ->all();

            $latest = $this->dedupeArticlesByIdAndExclude($latest, $highlightPayload['ids']);
            $latest = $this->dedupeArticlesBySlug($latest);
            $latest = $this->dedupeArticlesByTitle($latest);
            $latest = array_values(array_filter($latest, function (array $row) use ($locale): bool {
                $lang = $row['language'] ?? 'fr';

                return $lang === $locale || ($lang === null && $locale === 'fr');
            }));

            return [
                'articles' => $latest,
                'total' => $total,
                'page' => $page,
                'per_page' => $latestLimit,
            ];
        });

        $highlightArray = $highlightPayload['items'];
        while (count($highlightArray) < 5) {
            $highlightArray[] = null;
        }

        return [
            'highlight' => array_slice($highlightArray, 0, 5),
            'top_news' => $highlightPayload['top_news'],
            'featured' => [],
            'latest' => $latestPayload['articles'],
            'pagination' => $this->makePaginator($latestPayload['articles'], $latestPayload['total'], $latestPayload['per_page'], $latestPayload['page']),
            'categories' => $categoriesPayload,
        ];
    }

    public function getCategoryHubData(string $categorySlug, array $subCategorySlugs = [], string $locale = 'fr', int $page = 1): array
    {
        $normalizedSubCategorySlugs = array_values(array_unique(array_filter(
            array_map(static fn (mixed $slug): string => trim((string) $slug), $subCategorySlugs),
            static fn (string $slug): bool => $slug !== ''
        )));

        $signature = $categorySlug
            . '|sub:' . ($normalizedSubCategorySlugs !== [] ? implode('-', $normalizedSubCategorySlugs) : 'none')
            . '|page:' . $page;

        $payload = $this->cache->remember('category-hub', $locale, $signature, $this->ttl(), function () use ($categorySlug, $normalizedSubCategorySlugs, $locale, $page): array {
            $category = Category::where('slug', $categorySlug)->firstOrFail();
            $subCategories = $category->getDescriptionSubCategories();
            $availableSubCategorySlugs = collect($subCategories)
                ->pluck('slug')
                ->filter(static fn (mixed $slug): bool => is_string($slug) && trim($slug) !== '')
                ->values()
                ->all();

            $activeSubCategorySlugs = array_values(array_filter(
                $normalizedSubCategorySlugs,
                static fn (string $slug): bool => in_array($slug, $availableSubCategorySlugs, true)
            ));

            $query = Article::published()
                ->forLocale($locale)
                ->where('category_id', $category->id)
                ->with(['category', 'subCategory']);

            if ($activeSubCategorySlugs !== []) {
                $selectedTerms = collect($subCategories)
                    ->whereIn('slug', $activeSubCategorySlugs)
                    ->pluck('name')
                    ->filter(static fn (mixed $name): bool => is_string($name) && trim($name) !== '')
                    ->values();

                if ($selectedTerms->isNotEmpty()) {
                    $query->where(function ($builder) use ($selectedTerms): void {
                        foreach ($selectedTerms as $searchTerm) {
                            $like = '%' . addcslashes($searchTerm, '%_\\') . '%';
                            $builder->orWhere(function ($termQuery) use ($like): void {
                                $termQuery->where('title', 'like', $like)
                                    ->orWhere('content', 'like', $like)
                                    ->orWhere('excerpt', 'like', $like)
                                    ->orWhere('meta_title', 'like', $like)
                                    ->orWhere('meta_description', 'like', $like)
                                    ->orWhere('keywords', 'like', $like);
                            });
                        }
                    });
                }
            }

            $totalPublished = (clone $query)->count();
            $perPage = 24;
            $articles = (clone $query)
                ->orderByDesc('published_at')
                ->forPage($page, $perPage)
                ->get()
                ->map(fn (Article $article): array => $this->articleToArray($article))
                ->all();

            return [
                'category' => $this->categoryToArray($category, $locale),
                'description' => $category->description,
                'total_published' => $totalPublished,
                'active_sub_category_slugs' => $activeSubCategorySlugs,
                'sub_categories' => $subCategories,
                'articles' => $articles,
                'page' => $page,
                'per_page' => $perPage,
            ];
        });

        $currentSubNames = collect($payload['sub_categories'])
            ->whereIn('slug', $payload['active_sub_category_slugs'])
            ->pluck('name')
            ->filter(static fn (mixed $name): bool => is_string($name) && trim($name) !== '')
            ->values()
            ->all();

        return [
            'category' => $payload['category'],
            'description' => $payload['description'],
            'total_published' => $payload['total_published'],
            'current_sub_category_slug' => $payload['active_sub_category_slugs'][0] ?? null,
            'current_sub_category_name' => count($currentSubNames) === 1 ? $currentSubNames[0] : null,
            'current_sub_category_slugs' => $payload['active_sub_category_slugs'],
            'current_sub_category_names' => $currentSubNames,
            'sub_categories' => $payload['sub_categories'],
            'pagination' => $this->makePaginator($payload['articles'], $payload['total_published'], $payload['per_page'], $payload['page'])->withQueryString(),
            'articles' => $payload['articles'],
        ];
    }

    public function getRelatedArticlesData(Article $article, string $locale, int $target = 4): array
    {
        $signature = $article->id . ':target:' . $target;

        return $this->cache->remember('related-articles', $locale, $signature, $this->ttl(), function () use ($article, $locale, $target): array {
            $category = $article->category_id ? Category::find($article->category_id) : null;

            $primaryRelated = Article::published()
                ->forLocale($locale)
                ->with('category')
                ->where('id', '!=', $article->id)
                ->when($article->category_id, fn ($query) => $query->where('category_id', $article->category_id))
                ->orderByDesc('published_at')
                ->limit($target)
                ->get();

            $selectedIds = $primaryRelated->pluck('id')->prepend($article->id)->all();
            $missingCount = max(0, $target - $primaryRelated->count());

            $fallbackRelated = collect();
            if ($missingCount > 0) {
                $fallbackRelated = Article::published()
                    ->forLocale($locale)
                    ->with('category')
                    ->whereNotIn('id', $selectedIds)
                    ->orderByDesc('published_at')
                    ->limit($missingCount)
                    ->get();
            }

            return $primaryRelated
                ->concat($fallbackRelated)
                ->take($target)
                ->map(fn (Article $related) => [
                    'title' => $related->title,
                    'slug' => $related->slug,
                    'reading_time' => $related->reading_time,
                    'category' => $related->category?->name ?? '',
                    'published_at_display' => $related->published_at?->locale($locale)->isoFormat('D MMMM YYYY'),
                    'image' => ! empty($related->cover_image_url)
                        ? $related->cover_image_url
                        : vivat_category_fallback_image($related->category?->slug ?? $category?->slug, 760, 520, (string) $related->id, 'also'),
                    'fallback' => vivat_category_fallback_image(
                        $related->category?->slug ?? $category?->slug,
                        760,
                        520,
                        (string) $related->id,
                        'also'
                    ),
                ])
                ->values()
                ->all();
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $pageHits
     * @return array<int, array<string, mixed>>
     */
    private function padSearchMosaicArticles(string $locale, array $pageHits, int $target): array
    {
        if ($pageHits === [] || count($pageHits) >= $target) {
            return $pageHits;
        }

        $existingIds = collect($pageHits)->pluck('id')->filter()->unique()->values()->all();
        $needed = $target - count($pageHits);
        $referenceCategoryId = $pageHits[0]['category']['id'] ?? null;

        $fillQuery = Article::published()
            ->forLocale($locale)
            ->with('category')
            ->whereNotIn('id', $existingIds);

        if ($referenceCategoryId) {
            $fillQuery->orderByRaw('CASE WHEN category_id = ? THEN 0 ELSE 1 END', [$referenceCategoryId]);
        }

        $fill = $fillQuery
            ->orderByDesc('published_at')
            ->limit($needed)
            ->get()
            ->map(fn (Article $article): array => $this->articleToArray($article))
            ->all();

        return array_merge($pageHits, $fill);
    }

    /**
     * @param  array<int, array<string, mixed>>  $articles
     * @param  array<string>  $excludeIds
     * @return array<int, array<string, mixed>>
     */
    private function dedupeArticlesByIdAndExclude(array $articles, array $excludeIds): array
    {
        $seen = array_fill_keys($excludeIds, true);
        $out = [];

        foreach ($articles as $row) {
            $id = $row['id'] ?? null;
            if ($id === null || ! empty($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $out[] = $row;
        }

        return array_values($out);
    }

    /**
     * @param  array<int, array<string, mixed>>  $articles
     * @return array<int, array<string, mixed>>
     */
    private function dedupeArticlesBySlug(array $articles): array
    {
        $seenSlug = [];
        $out = [];

        foreach ($articles as $row) {
            $slug = $row['slug'] ?? null;
            if ($slug === null || $slug === '' || ! empty($seenSlug[$slug])) {
                continue;
            }

            $seenSlug[$slug] = true;
            $out[] = $row;
        }

        return array_values($out);
    }

    /**
     * @param  array<int, array<string, mixed>>  $articles
     * @return array<int, array<string, mixed>>
     */
    private function dedupeArticlesByTitle(array $articles): array
    {
        $seenTitle = [];
        $out = [];

        foreach ($articles as $row) {
            $title = $row['title'] ?? '';
            $key = mb_strtolower(trim((string) $title));

            if ($key === '' || ! empty($seenTitle[$key])) {
                continue;
            }

            $seenTitle[$key] = true;
            $out[] = $row;
        }

        return array_values($out);
    }

    private function articleToArray(Article $article): array
    {
        $category = null;
        if ($article->category_id) {
            $resolved = Category::find($article->category_id);
            $category = $resolved ? $this->categoryToArray($resolved) : null;
        }

        $cover = $article->cover_image_url;
        $useFallback = empty($cover)
            || (is_string($cover) && stripos($cover, 'picsum') !== false)
            || (is_string($cover)
                && ! str_starts_with($cover, 'http')
                && ! str_starts_with($cover, '/uploads/')
                && ! str_starts_with($cover, '/storage/')
                && ! str_starts_with($cover, 'data:image/'));

        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'meta_title' => $article->meta_title,
            'meta_description' => $article->meta_description,
            'reading_time' => $article->reading_time,
            'published_at' => $article->published_at?->format('d/m/Y'),
            'cover_image_url' => $useFallback ? null : $cover,
            'cover_video_url' => $article->cover_video_url,
            'uses_auto_image' => $useFallback,
            'article_type' => $article->article_type,
            'language' => $article->language ?? 'fr',
            'category' => $category,
            'keywords' => is_array($article->keywords) ? $article->keywords : [],
            'sub_category' => $article->relationLoaded('subCategory') && $article->subCategory
                ? [
                    'name' => $article->subCategory->name,
                    'slug' => $article->subCategory->slug,
                ]
                : null,
        ];
    }

    private function articleCoverOrFallback(Article $article, ?Category $category): ?string
    {
        $cover = $article->cover_image_url;

        if (empty($cover)
            || (is_string($cover) && stripos($cover, 'picsum') !== false)
            || (is_string($cover)
                && ! str_starts_with($cover, 'http')
                && ! str_starts_with($cover, '/uploads/')
                && ! str_starts_with($cover, '/storage/')
                && ! str_starts_with($cover, 'data:image/'))) {
            return null;
        }

        return $cover;
    }

    private function categoryToArray(Category $category, string $locale = ''): array
    {
        if ($locale === '') {
            $locale = app()->getLocale() ?: 'fr';
        }
        $fromDb = $category->image_url;
        $trimmed = is_string($fromDb) ? trim($fromDb) : '';
        $public = vivat_category_public_media_url($category->slug);
        $imageUrl = null;

        if ($trimmed !== '' && str_starts_with($trimmed, '/') && ! str_starts_with($trimmed, '//')) {
            $imageUrl = $trimmed;
        } elseif ($trimmed !== '' && filter_var($trimmed, FILTER_VALIDATE_URL)) {
            $imageUrl = $trimmed;
        } elseif ($public !== null && $public !== '') {
            $imageUrl = $public;
        }

        return [
            'id' => $category->id,
            'name' => $category->localizedName($locale),
            'slug' => $category->slug,
            'description' => $category->description,
            'image_url' => $imageUrl,
            'published_articles_count' => $category->published_articles_count ?? null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function makePaginator(array $items, int $total, int $perPage, int $page): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()?->url(),
                'pageName' => 'page',
            ],
        );
    }

    private function ttl(): int
    {
        return (int) config('vivat.public_cache_ttl', 900);
    }
}
