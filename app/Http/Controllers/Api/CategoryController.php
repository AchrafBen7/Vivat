<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\SubCategoryResource;
use App\Models\Article;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /* ================================================================== */
    /*  LIST & SHOW (public + admin)                                      */
    /* ================================================================== */

    public function index(Request $request): AnonymousResourceCollection
    {
        $locale = content_locale($request);
        $closure = function () use ($locale) {
            return Category::query()
                ->withCount(['articles as published_articles_count' => fn ($q) => $q->where('status', 'published')->where('language', $locale)])
                ->orderBy('name')
                ->get();
        };
        $categories = config('vivat.disable_page_cache') ? $closure() : Cache::remember('vivat.categories.index.' . $locale, 3600, $closure);

        return CategoryResource::collection($categories);
    }

    public function show(Request $request, Category $category): CategoryResource
    {
        $locale = content_locale($request);
        $category->loadCount(['articles as published_articles_count' => fn ($q) => $q->where('status', 'published')->where('language', $locale)]);

        return new CategoryResource($category);
    }

    /* ================================================================== */
    /*  HUB PAGE (public)                                                 */
    /* ================================================================== */

    /**
     * GET /api/public/categories/{slug}/hub
     * Page Hub : description, sous-catégories (max 5), articles à la une + derniers avec display_type.
     * Query: ?sub_category=slug pour filtrer par sous-catégorie.
     */
    public function hub(Request $request, Category $category): JsonResponse
    {
        $locale = content_locale($request);
        $subCategorySlug = $request->input('sub_category');
        $cacheKey = 'vivat.hub.'.$category->slug.($subCategorySlug ? '.'.(string) $subCategorySlug : '').'.'.$locale;
        $closure = function () use ($category, $subCategorySlug, $locale) {
            $category->load(['subCategories' => fn ($q) => $q->orderBy('order')]);

            $query = Article::published()
                ->forLocale($locale)
                ->where('category_id', $category->id)
                ->with(['category', 'subCategory']);

            if ($subCategorySlug) {
                $subCategory = SubCategory::where('category_id', $category->id)
                    ->where('slug', $subCategorySlug)
                    ->first();
                if ($subCategory) {
                    $query->where('sub_category_id', $subCategory->id);
                }
            }

            $totalPublished = (clone $query)->count();

            // À la une : 3 articles avec image ou vidéo, tri quality_score
            $featuredQuery = (clone $query)
                ->where(fn ($q) => $q->whereNotNull('cover_image_url')->orWhereNotNull('cover_video_url'))
                ->orderByDesc('quality_score')
                ->limit(3);
            $featured = $featuredQuery->get();

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
                'featured' => $featured,
                'latest' => $latest,
            ];
        };
        $data = config('vivat.disable_page_cache') ? $closure() : Cache::remember($cacheKey, 900, $closure);

        $requestForResource = $request;
        $featuredResources = $data['featured']->map(fn ($a) => array_merge(
            (new ArticleResource($a))->toArray($requestForResource),
            ['display_type' => 'featured']
        ));
        $latestWithDisplayType = $data['latest']->map(function ($article) use ($requestForResource) {
            $arr = (new ArticleResource($article))->toArray($requestForResource);
            $arr['display_type'] = ($article->cover_image_url || $article->cover_video_url) ? 'standard' : 'secondary';
            return $arr;
        });

        return response()->json([
            'category' => new CategoryResource($data['category']->load('subCategories')),
            'description' => $data['description'],
            'total_published' => $data['total_published'],
            'sub_categories' => SubCategoryResource::collection($data['category']->subCategories),
            'featured' => $featuredResources,
            'latest' => [
                'label' => 'Dernières actualités',
                'articles' => $latestWithDisplayType,
            ],
        ]);
    }

    /* ================================================================== */
    /*  CRUD (admin only)                                                 */
    /* ================================================================== */

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', 'unique:categories'],
            'description' => ['nullable', 'string', 'max:1000'],
            'home_order'  => ['nullable', 'integer', 'min:1', 'max:99'],
            'image_url'   => ['nullable', 'string', 'max:500'],
            'video_url'   => ['nullable', 'string', 'max:500'],
        ]);

        $category = Category::create($validated);
        $category->refresh(); // charge created_at défini par la BDD (useCurrent)
        foreach (['fr', 'nl'] as $loc) {
            Cache::forget('vivat.categories.index.' . $loc);
            Cache::forget('vivat.home.' . $loc);
        }

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function update(Request $request, Category $category): CategoryResource
    {
        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'slug'        => ['sometimes', 'string', 'max:255', 'unique:categories,slug,' . $category->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'home_order'  => ['nullable', 'integer', 'min:1', 'max:99'],
            'image_url'   => ['nullable', 'string', 'max:500'],
            'video_url'   => ['nullable', 'string', 'max:500'],
        ]);

        $category->update($validated);
        foreach (['fr', 'nl'] as $loc) {
            Cache::forget('vivat.categories.index.' . $loc);
            Cache::forget('vivat.hub.' . $category->slug . '.' . $loc);
            Cache::forget('vivat.home.' . $loc);
        }

        return new CategoryResource($category->fresh());
    }

    public function destroy(Category $category): JsonResponse
    {
        $slug = $category->slug;
        $category->delete();
        foreach (['fr', 'nl'] as $loc) {
            Cache::forget('vivat.categories.index.' . $loc);
            Cache::forget('vivat.hub.' . $slug . '.' . $loc);
            Cache::forget('vivat.home.' . $loc);
        }

        return response()->json(null, 204);
    }
}
