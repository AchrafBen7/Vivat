<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\PublicPageDataService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function index(PublicPageDataService $pageData): Response
    {
        $categories = Category::query()
            ->withCount(['articles as published_articles_count' => fn ($q) => $q->where('status', 'published')])
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
            'title' => 'Rubriques — Vivat',
            'meta_description' => 'Découvrez les rubriques Vivat.',
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function hub(Request $request, string $slug, PublicPageDataService $pageData): Response
    {
        $subCategorySlug = $request->input('sub_category');
        $data = $pageData->getCategoryHubData($slug, $subCategorySlug);

        $content = render_php_view('site.category_hub', $data);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'title' => ($data['category']['name'] ?? 'Rubrique') . ' — Vivat',
            'meta_description' => $data['description'] ?? '',
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
