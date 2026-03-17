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
                'pagination' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12),
                'search_term' => $q,
                'matched_category' => null,
            ];
        }

        // Si le terme correspond à une catégorie (nom ou slug), filtrer par cette catégorie
        $matchedCategory = Category::whereRaw('LOWER(name) = ?', [$normalized])
            ->orWhereRaw('LOWER(slug) = ?', [$normalized])
            ->first();

        if ($matchedCategory) {
            $query->where('category_id', $matchedCategory->id);
        } elseif ($q !== '') {
            // Recherche textuelle dans les articles
            if (DB::getDriverName() === 'mysql' && strlen($q) >= 2) {
                $query->where(function ($builder) use ($q) {
                    $builder->whereFullText(['title', 'excerpt'], $q)
                        ->orWhere('meta_description', 'LIKE', '%' . addcslashes($q, '%_\\') . '%');
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

        $articles = $query->orderByDesc('published_at')->paginate(12);

        return [
            'articles' => $articles->getCollection()->map(fn ($a) => $this->articleToArray($a))->all(),
            'pagination' => $articles,
            'search_term' => $q,
            'matched_category' => $matchedCategory ? $this->categoryToArray($matchedCategory) : null,
        ];
    }

    public function getHomeData(string $locale = 'fr'): array
    {
        $cacheKey = 'vivat.home.' . $locale;
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
                ->when(
                    Category::whereNotNull('home_order')->exists(),
                    fn ($q) => $q->whereNotNull('home_order')->orderBy('home_order'),
                    fn ($q) => $q->orderBy('name')
                )
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

    public function getCategoryHubData(string $categorySlug, ?string $subCategorySlug = null, string $locale = 'fr', int $page = 1): array
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $cacheKey = 'vivat.hub.' . $category->slug . ($subCategorySlug ? '.' . $subCategorySlug : '') . '.' . $locale . '.page.' . $page;
        $closure = function () use ($category, $subCategorySlug, $locale, $page) {
            // Sous-catégories = termes extraits de la description (ex. "Innovation, tech, numérique" → Innovation, tech, numérique)
            $subCategories = $category->getDescriptionSubCategories();

            $query = Article::published()
                ->forLocale($locale)
                ->where('category_id', $category->id)
                ->with(['category', 'subCategory']);

            if ($subCategorySlug) {
                $term = collect($subCategories)->firstWhere('slug', $subCategorySlug);
                if ($term) {
                    $searchTerm = $term['name'];
                    $query->where(function ($q) use ($searchTerm) {
                        $like = '%' . addcslashes($searchTerm, '%_\\') . '%';
                        $q->where('title', 'like', $like)
                            ->orWhere('content', 'like', $like)
                            ->orWhere('excerpt', 'like', $like)
                            ->orWhere('meta_title', 'like', $like)
                            ->orWhere('meta_description', 'like', $like)
                            ->orWhere('keywords', 'like', $like);
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
                'sub_categories' => $subCategories,
                'articles' => $articles,
            ];
        };
        $data = config('vivat.disable_page_cache') ? $closure() : Cache::remember($cacheKey, 900, $closure);

        $articlesPaginator = $data['articles'];
        $articlesCollection = $articlesPaginator->getCollection();
        $articlesCollection->load('category');

        // sub_categories = termes extraits de la description (name + slug)
        $subCategories = $data['sub_categories'] ?? [];
        $currentSubName = null;
        if ($subCategorySlug) {
            $match = collect($subCategories)->firstWhere('slug', $subCategorySlug);
            $currentSubName = $match['name'] ?? null;
        }

        return [
            'category' => $this->categoryToArray($data['category']),
            'description' => $data['description'],
            'total_published' => $data['total_published'],
            'current_sub_category_slug' => $subCategorySlug,
            'current_sub_category_name' => $currentSubName,
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
            || (is_string($cover) && ! str_starts_with($cover, 'http'));

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
            'article_type' => $a->article_type,
            'language' => $a->language ?? 'fr',
            'category' => $category,
        ];
    }

    private function categoryToArray(Category $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'description' => $c->description,
            'image_url' => $c->image_url,
            'published_articles_count' => $c->published_articles_count ?? null,
        ];
    }
}
