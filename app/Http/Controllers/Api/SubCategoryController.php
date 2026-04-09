<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubCategoryResource;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    /**
     * GET /api/categories/{category}/sub-categories (admin)
     */
    public function index(Category $category): JsonResponse
    {
        $items = $category->subCategories()->orderBy('order')->get();

        return response()->json(SubCategoryResource::collection($items));
    }

    /**
     * POST /api/categories/{category}/sub-categories (admin) max 5 par catégorie
     */
    public function store(Request $request, Category $category): JsonResponse
    {
        $count = $category->subCategories()->count();
        if ($count >= 5) {
            return response()->json([
                'message' => 'Maximum 5 sous-catégories par catégorie.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'order' => ['nullable', 'integer', 'min:1', 'max:5'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'video_url' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['category_id'] = $category->id;
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['slug']);

        if (SubCategory::where('category_id', $category->id)->where('slug', $validated['slug'])->exists()) {
            return response()->json(['message' => 'Une sous-catégorie avec ce slug existe déjà dans cette catégorie.'], 422);
        }

        $subCategory = SubCategory::create($validated);
        $subCategory->refresh();
        $this->forgetHubCache($category);

        return (new SubCategoryResource($subCategory))->response()->setStatusCode(201);
    }

    /**
     * PUT /api/sub-categories/{subCategory} (admin)
     */
    public function update(Request $request, SubCategory $subCategory): SubCategoryResource
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'order' => ['nullable', 'integer', 'min:1', 'max:5'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'video_url' => ['nullable', 'string', 'max:500'],
        ]);

        if (isset($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['slug']);
        }

        $subCategory->update($validated);
        $this->forgetHubCache($subCategory->category);

        return new SubCategoryResource($subCategory->fresh());
    }

    /**
     * DELETE /api/sub-categories/{subCategory} (admin)
     */
    public function destroy(SubCategory $subCategory): JsonResponse
    {
        $category = $subCategory->category;
        $subSlug = $subCategory->slug;
        $catSlug = $category->slug;
        $subCategory->delete();
        \Illuminate\Support\Facades\Cache::forget('vivat.hub.' . $catSlug);
        \Illuminate\Support\Facades\Cache::forget('vivat.hub.' . $catSlug . '.' . $subSlug);

        return response()->json(null, 204);
    }

    private function forgetHubCache(Category $category): void
    {
        \Illuminate\Support\Facades\Cache::forget('vivat.hub.' . $category->slug);
        $category->load('subCategories');
        foreach ($category->subCategories as $sub) {
            \Illuminate\Support\Facades\Cache::forget('vivat.hub.' . $category->slug . '.' . $sub->slug);
        }
    }
}
