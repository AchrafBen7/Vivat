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
    public function getHomeData(): array
    {
        $cacheKey = 'vivat.home';
        $cacheTtl = (int) config('vivat.home_cache_ttl', 300);

        $data = Cache::remember($cacheKey, $cacheTtl, function () {
            $topNews = Article::published()
                ->where('article_type', 'hot_news')
                ->with('category')
                ->orderByDesc('published_at')
                ->first();

            $featuredLimit = (int) config('vivat.home_featured_count', 4);
            $latestLimit = (int) config('vivat.home_latest_count', 12);
            $categoriesLimit = (int) config('vivat.home_categories_count', 9);

            $baseQuery = Article::published()->with('category')->orderByDesc('published_at');
            $excludeId = $topNews?->id;

            $featuredQuery = clone $baseQuery;
            if ($excludeId) {
                $featuredQuery->where('id', '!=', $excludeId);
            }
            $featuredQuery->where(fn ($q) => $q->where('article_type', 'hot_news')->orWhereNotNull('cover_image_url'));
            $featured = $featuredQuery->limit($featuredLimit)->get();

            $featuredIds = $featured->pluck('id')->push($excludeId)->filter()->all();
            $latestQuery = Article::published()->with('category')->orderByDesc('published_at');
            if (count($featuredIds) > 0) {
                $latestQuery->whereNotIn('id', $featuredIds);
            }
            $latest = $latestQuery->limit($latestLimit)->get();

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
                'top_news' => $topNews,
                'featured' => $featured,
                'latest' => $latest,
                'categories' => $categories,
            ];
        });

        return [
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
            $featured = (clone $query)
                ->where(fn ($q) => $q->whereNotNull('cover_image_url')->orWhereNotNull('cover_video_url'))
                ->orderByDesc('quality_score')
                ->limit(3)
                ->get();
            $excludeIds = $featured->pluck('id')->all();
            $latestQuery = (clone $query)->orderByDesc('published_at')->limit(12);
            if (count($excludeIds) > 0) {
                $latestQuery->whereNotIn('id', $excludeIds);
            }
            $latest = $latestQuery->get();

            return [
                'category' => $category,
                'description' => $category->description,
                'total_published' => $totalPublished,
                'sub_categories' => $category->subCategories,
                'featured' => $featured,
                'latest' => $latest,
            ];
        });

        return [
            'category' => $this->categoryToArray($data['category']->load('subCategories')),
            'description' => $data['description'],
            'total_published' => $data['total_published'],
            'sub_categories' => $data['sub_categories']->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'slug' => $s->slug,
            ])->all(),
            'featured' => $data['featured']->map(fn ($a) => $this->articleToArray($a))->all(),
            'latest' => $data['latest']->map(fn ($a) => $this->articleToArray($a))->all(),
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
            'published_at' => $a->published_at?->format('d/m/Y H:i'),
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
