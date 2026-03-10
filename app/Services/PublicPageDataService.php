<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;

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

            // "Dernières actualités" = les plus récents après les 5 highlight, sans aucun doublon ni article déjà en highlight
            $restCount = max($featuredLimit + $latestLimit, 20);
            $latest = Article::published()
                ->forLocale($locale)
                ->with('category')
                ->whereNotIn('id', $highlightIds)
                ->orderByDesc('published_at')
                ->limit($restCount)
                ->get()
                ->filter(fn ($a) => ! in_array($a->id, $highlightIds, true))
                ->filter(fn ($a) => $a->language === $locale || ($a->language === null && $locale === 'fr'))
                ->unique('id')
                ->values()
                ->take($restCount);
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
        $latestCollection = EloquentCollection::make($data['latest'] ?? []);
        $latestCollection->load('category');
        $featuredCollection->load('category');

        $highlightIdsForLatest = $highlightCollection->pluck('id')->unique()->all();
        $latestAsArray = $latestCollection->map(fn ($a) => $this->articleToArray($a))->all();
        $latestAsArray = $this->dedupeArticlesByIdAndExclude($latestAsArray, $highlightIdsForLatest);
        $latestAsArray = $this->dedupeArticlesBySlug($latestAsArray);
        // Garde-fou : ne jamais inclure un article dont la langue ne correspond pas (évite NL quand locale = fr)
        $latestAsArray = array_values(array_filter($latestAsArray, function ($row) use ($locale) {
            $lang = $row['language'] ?? 'fr';
            return $lang === $locale || ($lang === null && $locale === 'fr');
        }));

        return [
            'highlight' => $highlightArray,
            'top_news' => $topNews instanceof Article ? $this->articleToArray($topNews) : null,
            'featured' => $featuredCollection->map(fn ($a) => $this->articleToArray($a))->all(),
            'latest' => $latestAsArray,
            'categories' => ($data['categories'] ?? collect())->map(fn ($c) => $this->categoryToArray($c))->all(),
        ];
    }

    public function getCategoryHubData(string $categorySlug, ?string $subCategorySlug = null, string $locale = 'fr'): array
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $cacheKey = 'vivat.hub.' . $category->slug . ($subCategorySlug ? '.' . $subCategorySlug : '') . '.' . $locale;
        $closure = function () use ($category, $subCategorySlug, $locale) {
            $category->load(['subCategories' => fn ($q) => $q->orderBy('order')]);

            $query = Article::published()
                ->forLocale($locale)
                ->where('category_id', $category->id)
                ->with(['category', 'subCategory']);

            if ($subCategorySlug) {
                $sub = \App\Models\SubCategory::where('category_id', $category->id)->where('slug', $subCategorySlug)->first();
                if ($sub) {
                    $query->where('sub_category_id', $sub->id);
                }
            }

            $totalPublished = (clone $query)->count();
            $articles = (clone $query)->orderByDesc('published_at')->limit(24)->get()->unique('id')->values();

            return [
                'category' => $category,
                'description' => $category->description,
                'total_published' => $totalPublished,
                'sub_categories' => $category->subCategories,
                'articles' => $articles,
            ];
        };
        $data = config('vivat.disable_page_cache') ? $closure() : Cache::remember($cacheKey, 900, $closure);

        $articlesCollection = EloquentCollection::make($data['articles'] ?? []);
        $articlesCollection->load('category');

        return [
            'category' => $this->categoryToArray($data['category']->load('subCategories')),
            'description' => $data['description'],
            'total_published' => $data['total_published'],
            'current_sub_category_slug' => $subCategorySlug,
            'sub_categories' => $data['sub_categories']->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'slug' => $s->slug,
            ])->all(),
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

    private function articleToArray(Article $a): array
    {
        // Toujours résoudre la catégorie depuis la DB via category_id pour garantir la cohérence (éviter affichage mauvaise catégorie)
        $category = null;
        if ($a->category_id) {
            $resolved = Category::find($a->category_id);
            $category = $resolved ? $this->categoryToArray($resolved) : null;
        }

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
            'cover_image_url' => $a->cover_image_url,
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
