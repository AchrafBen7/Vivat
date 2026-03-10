<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
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

            // Highlight = 5 emplacements : d'abord tous les hot_news (jusqu'à 5), puis compléter avec des featured
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
            }

            $highlightIds = $highlight->pluck('id')->all();

            // "Dernières actualités" = les plus récents après les 5 highlight (une seule liste par date)
            $restCount = max($featuredLimit + $latestLimit, 16);
            $latest = Article::published()
                ->with('category')
                ->whereNotIn('id', $highlightIds)
                ->orderByDesc('published_at')
                ->limit($restCount)
                ->get();
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

        $highlightArray = $data['highlight']->map(fn ($a) => $this->articleToArray($a))->values()->all();
        // S'assurer d'avoir exactement 5 slots (null si pas assez d'articles)
        while (count($highlightArray) < 5) {
            $highlightArray[] = null;
        }
        $highlightArray = array_slice($highlightArray, 0, 5);

        return [
            'highlight' => $highlightArray,
            'top_news' => $data['top_news'] ? $this->articleToArray($data['top_news']) : null,
            'featured' => $data['featured']->map(fn ($a) => $this->articleToArray($a))->all(),
            'latest' => $data['latest']->map(fn ($a) => $this->articleToArray($a))->all(),
            'categories' => $data['categories']->map(fn ($c) => $this->categoryToArray($c))->all(),
        ];
    }

    public function getCategoryHubData(string $categorySlug, ?string $subCategorySlug = null): array
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $cacheKey = 'vivat.hub.' . $category->slug . ($subCategorySlug ? '.' . $subCategorySlug : '');

        $data = Cache::remember($cacheKey, 900, function () use ($category, $subCategorySlug) {
            $category->load(['subCategories' => fn ($q) => $q->orderBy('order')->limit(5)]);

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
            $articles = (clone $query)->orderByDesc('published_at')->limit(16)->get();

            return [
                'category' => $category,
                'description' => $category->description,
                'total_published' => $totalPublished,
                'sub_categories' => $category->subCategories,
                'articles' => $articles,
            ];
        });

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
            'articles' => $data['articles']->map(fn ($a) => $this->articleToArray($a))->all(),
        ];
    }

    private function articleToArray(Article $a): array
    {
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
            'category' => $a->relationLoaded('category') ? $this->categoryToArray($a->category) : null,
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
