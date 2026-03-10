<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;

/**
 * Données pour les pages publiques (site HTML).
 * Réutilise la même logique / cache que l'API pour cohérence.
 */
class PublicPageDataService
{
    public function getArticlesIndexData(): array
    {
        $articles = Article::published()
            ->with('category')
            ->orderByDesc('published_at')
            ->paginate(12);

        return [
            'articles' => $articles->getCollection()->map(fn ($a) => $this->articleToArray($a))->all(),
            'pagination' => $articles,
        ];
    }

    public function getHomeData(): array
    {
        $cacheKey = 'vivat.home';
        $cacheTtl = (int) config('vivat.home_cache_ttl', 300);

        $data = Cache::remember($cacheKey, $cacheTtl, function () {
            $highlightSize = 5;
            $featuredLimit = (int) config('vivat.home_featured_count', 4);
            $latestLimit = (int) config('vivat.home_latest_count', 12);
            $categoriesLimit = (int) config('vivat.home_categories_count', 9);

            // Highlight = 5 emplacements : d'abord hot_news (jusqu'à 5), puis avec image, puis n'importe quel publié
            $hotNewsForHighlight = Article::published()
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

            $highlightIds = $highlight->pluck('id')->all();

            // "Dernières actualités" = les plus récents après les 5 highlight (une seule liste par date)
            $restCount = max($featuredLimit + $latestLimit, 16);
            $latest = Article::published()
                ->with('category')
                ->whereNotIn('id', $highlightIds)
                ->orderByDesc('published_at')
                ->limit($restCount)
                ->get()
                ->unique('id')
                ->values();
            $featured = collect();

            $categories = Category::query()
                ->withCount(['articles as published_articles_count' => fn ($q) => $q->where('status', 'published')])
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
        });

        $highlightCollection = EloquentCollection::make($data['highlight'] ?? []);
        $highlightCollection->load('category');
        $highlightArray = $highlightCollection->map(fn ($a) => $this->articleToArray($a))->values()->all();
        while (count($highlightArray) < 5) {
            $highlightArray[] = null;
        }
        $highlightArray = array_slice($highlightArray, 0, 5);

        $topNews = $data['top_news'] ?? $highlightCollection->first();
        $featuredCollection = EloquentCollection::make($data['featured'] ?? []);
        $latestCollection = EloquentCollection::make($data['latest'] ?? []);
        $latestCollection->load('category');
        $featuredCollection->load('category');

        return [
            'highlight' => $highlightArray,
            'top_news' => $topNews instanceof Article ? $this->articleToArray($topNews) : null,
            'featured' => $featuredCollection->map(fn ($a) => $this->articleToArray($a))->all(),
            'latest' => $latestCollection->map(fn ($a) => $this->articleToArray($a))->all(),
            'categories' => ($data['categories'] ?? collect())->map(fn ($c) => $this->categoryToArray($c))->all(),
        ];
    }

    public function getCategoryHubData(string $categorySlug, ?string $subCategorySlug = null): array
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $cacheKey = 'vivat.hub.' . $category->slug . ($subCategorySlug ? '.' . $subCategorySlug : '');

        $data = Cache::remember($cacheKey, 900, function () use ($category, $subCategorySlug) {
            $category->load(['subCategories' => fn ($q) => $q->orderBy('order')]);

            $query = Article::published()
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
        });

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

    private function articleToArray(Article $a): array
    {
        $category = null;
        if ($a->relationLoaded('category') && $a->category !== null && $a->category->id === $a->category_id) {
            $category = $this->categoryToArray($a->category);
        } elseif ($a->category_id) {
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
