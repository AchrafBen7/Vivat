<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Données pour les pages publiques (site HTML).
 * Réutilise la même logique / cache que l'API pour cohérence.
 * Toutes les listes d'articles sont filtrées par langue (fr ou nl).
 */
class PublicPageDataService
{
    public function getArticlesIndexData(string $locale = 'fr'): array
    {
        $articles = Article::published()
            ->forLocale($locale)
            ->with('category')
            ->orderByDesc('published_at')
            ->paginate(12);

        return [
            'articles' => $articles->getCollection()->map(fn ($a) => $this->articleToArray($a))->all(),
            'pagination' => $articles,
        ];
    }

    /**
     * Données pour la page de recherche.
     * Si le terme correspond à une catégorie (nom ou slug), filtre par cette catégorie.
     * Sinon, recherche full-text dans title, excerpt, meta_description.
     */
    public function getSearchData(string $locale = 'fr', string $q = ''): array
    {
        $query = Article::published()
            ->forLocale($locale)
            ->with('category');

        $matchedCategory = null;
        $normalized = strtolower(trim($q));

        // Sans terme de recherche, ne rien retourner
        if ($normalized === '') {
            return [
                'articles' => [],
                'pagination' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                'search_term' => $q,
                'matched_category' => null,
                'search_mosaic_padded' => false,
                'continue_reading_articles' => [],
            ];
        }

        // Si le terme correspond à une catégorie (nom ou slug), filtrer par cette catégorie
        $matchedCategory = Category::where('name', $normalized)
            ->orWhere('slug', $normalized)
            ->first();

        if ($matchedCategory) {
            $query->where('category_id', $matchedCategory->id);
        } elseif ($q !== '') {
            // Recherche textuelle dans les articles
            if (DB::getDriverName() === 'mysql' && strlen($q) >= 2) {
                $query->where(function ($builder) use ($q) {
                    $builder->whereFullText(['title', 'excerpt'], $q)
                        ->orWhere('meta_description', 'LIKE', '%'.addcslashes($q, '%_\\').'%');
                });
            } else {
                $query->where(function ($builder) use ($q) {
                    $esc = addcslashes($q, '%_\\');
                    $builder->where('title', 'LIKE', "%{$esc}%")
                        ->orWhere('excerpt', 'LIKE', "%{$esc}%")
                        ->orWhere('meta_description', 'LIKE', "%{$esc}%");
                });
            }
        }

        $articles = $query->orderByDesc('published_at')->paginate(10);
        $currentArticles = $articles->getCollection();

        /** @var array<int, array<string, mixed>> $pageHits */
        $pageHits = $currentArticles->map(fn ($a) => $this->articleToArray($a))->values()->all();

        /** Mosaïque 10 cartes (ordre séquentiel) : compléter si la page en contient moins de 10. */
        $mosaicTarget = 10;
        $mosaicArticles = $this->padSearchMosaicArticles($locale, $pageHits, $mosaicTarget);
        $mosaicPadded = count($pageHits) > 0 && count($mosaicArticles) > count($pageHits);

        /** @var array<int, string> $excludeIds */
        $excludeIds = collect($mosaicArticles)->pluck('id')->filter()->unique()->values()->all();

        /** @var array<int, array<string, mixed>> $continueReadingArticles */
        $continueReadingArticles = Article::published()
            ->forLocale($locale)
            ->with('category')
            ->when($excludeIds !== [], fn ($query) => $query->whereNotIn('id', $excludeIds))
            ->orderByDesc('published_at')
            ->limit(20)
            ->get()
            ->map(fn (Article $a) => $this->articleToArray($a))
            ->values()
            ->all();

        return [
            'articles' => $mosaicArticles,
            'pagination' => $articles,
            'search_term' => $q,
            'matched_category' => $matchedCategory ? $this->categoryToArray($matchedCategory) : null,
            'search_mosaic_padded' => $mosaicPadded,
            'continue_reading_articles' => $continueReadingArticles,
        ];
    }

    /**
     * Complète la liste d’articles (page courante) jusqu’à $target pour remplir la mosaïque recherche (10 cartes).
     *
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
            ->map(fn (Article $a) => $this->articleToArray($a))
            ->all();

        return array_merge($pageHits, $fill);
    }

    public function getHomeData(string $locale = 'fr'): array
    {
        $cacheKey = config('vivat.home_cache_key_prefix', 'vivat.home.v2').'.'.$locale;
        $cacheTtl = (int) config('vivat.home_cache_ttl', 300);
        $closure = function () use ($locale) {
            $highlightSize = 5;
            $featuredLimit = (int) config('vivat.home_featured_count', 4);
            $latestLimit = (int) config('vivat.home_latest_count', 12);
            $categoriesLimit = (int) config('vivat.home_categories_count', 9);

            // Highlight = 5 emplacements : d'abord hot_news (jusqu'à 5), puis avec image, puis n'importe quel publié
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
                    ->where(fn ($q) => $q->where('article_type', 'hot_news')->orWhereNotNull('cover_image_url'))
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

            // Premier slot (h0) = grande carte : privilégier un article avec image si possible
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

            $highlightIds = $highlight->pluck('id')->unique()->values()->all();

            // "Dernières actualités" = les plus récents (published_at desc) déjà exclus des 5 highlight, sans doublon (id, slug, titre).
            // L'ordre est conservé après déduplication pour que la vue affiche bien du plus récent au moins récent.
            $fetchLatest = $latestLimit + 30;
            $latest = Article::published()
                ->forLocale($locale)
                ->with('category')
                ->whereNotIn('id', $highlightIds)
                ->orderByDesc('published_at')
                ->limit($fetchLatest)
                ->get()
                ->filter(fn ($a) => ! in_array($a->id, $highlightIds, true))
                ->filter(fn ($a) => $a->language === $locale || ($a->language === null && $locale === 'fr'))
                ->unique('id')
                ->values();
            $featured = collect();

            $categories = Category::query()
                ->withCount(['articles as published_articles_count' => fn ($q) => $q->where('status', 'published')->where('language', $locale)])
                ->orderedForHome()
                ->limit($categoriesLimit)
                ->get();

            return [
                'highlight' => $highlight,
                'top_news' => $highlight->first(),
                'featured' => $featured,
                'latest' => $latest,
                'categories' => $categories,
            ];
        };
        $data = config('vivat.disable_page_cache') ? $closure() : Cache::remember($cacheKey, $cacheTtl, $closure);

        $highlightCollection = EloquentCollection::make($data['highlight'] ?? []);
        $highlightCollection->load('category');
        $highlightArray = $highlightCollection
            ->filter(fn ($a) => $a->language === $locale || ($a->language === null && $locale === 'fr'))
            ->map(fn ($a) => $this->articleToArray($a))
            ->values()
            ->all();
        while (count($highlightArray) < 5) {
            $highlightArray[] = null;
        }
        $highlightArray = array_slice($highlightArray, 0, 5);

        $topNews = $data['top_news'] ?? $highlightCollection->first();
        $featuredCollection = EloquentCollection::make($data['featured'] ?? []);
        $featuredCollection->load('category');

        $highlightIdsForLatest = $highlightCollection->pluck('id')->unique()->all();
        $latestLimit = (int) config('vivat.home_latest_count', 12);
        $latestPaginator = Article::published()
            ->forLocale($locale)
            ->with('category')
            ->whereNotIn('id', $highlightIdsForLatest)
            ->orderByDesc('published_at')
            ->paginate($latestLimit);

        $latestAsArray = $latestPaginator->getCollection()
            ->map(fn ($article) => $this->articleToArray($article))
            ->all();
        $latestAsArray = $this->dedupeArticlesByIdAndExclude($latestAsArray, $highlightIdsForLatest);
        $latestAsArray = $this->dedupeArticlesBySlug($latestAsArray);
        $latestAsArray = $this->dedupeArticlesByTitle($latestAsArray);
        $latestAsArray = array_values(array_filter($latestAsArray, function ($row) use ($locale) {
            $lang = $row['language'] ?? 'fr';

            return $lang === $locale || ($lang === null && $locale === 'fr');
        }));
        $latestPaginator->setCollection(collect($latestAsArray));

        return [
            'highlight' => $highlightArray,
            'top_news' => $topNews instanceof Article ? $this->articleToArray($topNews) : null,
            'featured' => $featuredCollection->map(fn ($a) => $this->articleToArray($a))->all(),
            'latest' => $latestAsArray,
            'pagination' => $latestPaginator,
            'categories' => ($data['categories'] ?? collect())->map(fn ($c) => $this->categoryToArray($c))->all(),
        ];
    }

    public function getCategoryHubData(string $categorySlug, array $subCategorySlugs = [], string $locale = 'fr', int $page = 1): array
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $normalizedSubCategorySlugs = array_values(array_unique(array_filter(
            array_map(static fn (mixed $slug): string => trim((string) $slug), $subCategorySlugs),
            static fn (string $slug): bool => $slug !== ''
        )));
        $cacheKey = 'vivat.hub.'.$category->slug.($normalizedSubCategorySlugs !== [] ? '.'.implode('-', $normalizedSubCategorySlugs) : '').'.'.$locale.'.page.'.$page;
        $closure = function () use ($category, $normalizedSubCategorySlugs, $locale, $page) {
            // Sous-catégories = termes extraits de la description (ex. "Innovation, tech, numérique" → Innovation, tech, numérique)
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
                    $query->where(function ($q) use ($selectedTerms) {
                        foreach ($selectedTerms as $searchTerm) {
                            $like = '%'.addcslashes($searchTerm, '%_\\').'%';
                            $q->orWhere(function ($termQuery) use ($like) {
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
            $articles = (clone $query)
                ->orderByDesc('published_at')
                ->paginate(24, ['*'], 'page', $page);

            return [
                'category' => $category,
                'description' => $category->description,
                'total_published' => $totalPublished,
                'active_sub_category_slugs' => $activeSubCategorySlugs,
                'sub_categories' => $subCategories,
                'articles' => $articles,
            ];
        };
        $data = config('vivat.disable_page_cache') ? $closure() : Cache::remember($cacheKey, 900, $closure);

        $articlesPaginator = $data['articles'];
        $articlesCollection = $articlesPaginator->getCollection();
        $articlesCollection->load(['category', 'subCategory']);

        // sub_categories = termes extraits de la description (name + slug)
        $subCategories = $data['sub_categories'] ?? [];
        $activeSubCategorySlugs = $data['active_sub_category_slugs'] ?? [];
        $currentSubNames = collect($subCategories)
            ->whereIn('slug', $activeSubCategorySlugs)
            ->pluck('name')
            ->filter(static fn (mixed $name): bool => is_string($name) && trim($name) !== '')
            ->values()
            ->all();

        return [
            'category' => $this->categoryToArray($data['category']),
            'description' => $data['description'],
            'total_published' => $data['total_published'],
            'current_sub_category_slug' => $activeSubCategorySlugs[0] ?? null,
            'current_sub_category_name' => count($currentSubNames) === 1 ? $currentSubNames[0] : null,
            'current_sub_category_slugs' => $activeSubCategorySlugs,
            'current_sub_category_names' => $currentSubNames,
            'sub_categories' => $subCategories,
            'pagination' => $articlesPaginator->withQueryString(),
            'articles' => $articlesCollection->map(fn ($a) => $this->articleToArray($a))->all(),
        ];
    }

    /**
     * Retourne une liste d'articles (tableaux assoc) sans doublon d'id et en excluant les ids donnés.
     *
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
     * Garde une seule occurrence par slug (évite le même article affiché 2 fois si 2 lignes en base).
     *
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
     * Garde une seule occurrence par titre normalisé (évite deux articles avec le même titre dans "Dernières actualités").
     *
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

    private function articleToArray(Article $a): array
    {
        // Toujours résoudre la catégorie depuis la DB via category_id pour garantir la cohérence (éviter affichage mauvaise catégorie)
        $category = null;
        if ($a->category_id) {
            $resolved = Category::find($a->category_id);
            $category = $resolved ? $this->categoryToArray($resolved) : null;
        }

        $cover = $a->cover_image_url;
        $useFallback = empty($cover)
            || (is_string($cover) && stripos($cover, 'picsum') !== false)
            || (is_string($cover) && ! str_starts_with($cover, 'http') && ! str_starts_with($cover, '/uploads/'));

        return [
            'id' => $a->id,
            'title' => $a->title,
            'slug' => $a->slug,
            'excerpt' => $a->excerpt,
            'content' => $a->content,
            'meta_title' => $a->meta_title,
            'meta_description' => $a->meta_description,
            'reading_time' => $a->reading_time,
            'published_at' => $a->published_at?->format('d/m/Y'),
            'cover_image_url' => $useFallback
                ? vivat_category_fallback_image(
                    $category['slug'] ?? null,
                    800,
                    450,
                    (string) $a->id,
                    'page'
                )
                : $cover,
            'cover_video_url' => $a->cover_video_url,
            'uses_auto_image' => $useFallback,
            'article_type' => $a->article_type,
            'language' => $a->language ?? 'fr',
            'category' => $category,
            'keywords' => is_array($a->keywords) ? $a->keywords : [],
            'sub_category' => $a->relationLoaded('subCategory') && $a->subCategory
                ? [
                    'name' => $a->subCategory->name,
                    'slug' => $a->subCategory->slug,
                ]
                : null,
        ];
    }

    private function categoryToArray(Category $c): array
    {
        // Médias rubrique : accepte un chemin local public/ ou une URL externe explicite (Cloudinary, CDN, etc.).
        $fromDb = $c->image_url;
        $trimmed = is_string($fromDb) ? trim($fromDb) : '';
        $public = vivat_category_public_media_url($c->slug);
        $imageUrl = null;
        if ($trimmed !== '' && str_starts_with($trimmed, '/') && ! str_starts_with($trimmed, '//')) {
            $imageUrl = $trimmed;
        } elseif ($trimmed !== '' && filter_var($trimmed, FILTER_VALIDATE_URL)) {
            $imageUrl = $trimmed;
        } elseif ($public !== null && $public !== '') {
            $imageUrl = $public;
        }

        return [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'description' => $c->description,
            'image_url' => $imageUrl,
            'published_articles_count' => $c->published_articles_count ?? null,
        ];
    }
}
