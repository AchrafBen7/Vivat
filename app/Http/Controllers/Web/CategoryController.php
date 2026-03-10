<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\PublicPageDataService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function index(Request $request, PublicPageDataService $pageData): Response
    {
        $locale = content_locale($request);
        $categories = Category::query()
            ->withCount(['articles as published_articles_count' => fn ($q) => $q->where('status', 'published')->where('language', $locale)])
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'description' => $c->description,
                'image_url' => $c->image_url,
                'published_articles_count' => $c->published_articles_count,
            ])
            ->all();

        $data = ['categories' => $categories];
        $content = render_php_view('site.categories', $data);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => 'Rubriques — Vivat',
            'meta_description' => 'Découvrez les rubriques Vivat. Parcourez nos catégories d\'actualités.',
            'canonical_url' => url('/categories'),
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function hub(Request $request, string $slug, PublicPageDataService $pageData): Response
    {
        $locale = content_locale($request);
        $subCategorySlug = $request->input('sub_category');
        $data = $pageData->getCategoryHubData($slug, $subCategorySlug, $locale);

        $categorySlug = $data['category']['slug'] ?? $slug;
        $content = render_php_view('site.category_hub', $data);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => ($data['category']['name'] ?? 'Rubrique') . ' — Vivat',
            'meta_description' => $data['description'] ?? 'Articles de la rubrique '.($data['category']['name'] ?? '').' sur Vivat.',
            'canonical_url' => url('/categories/'.$categorySlug),
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
