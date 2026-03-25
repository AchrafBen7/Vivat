<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PublicPageDataService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function hub(Request $request, string $slug, PublicPageDataService $pageData): Response
    {
        $locale = content_locale($request);
        if (! $request->filled('lang')) {
            $locale = 'fr';
        }
        $subCategoryInput = $request->input('sub_category', []);
        $subCategorySlugs = is_array($subCategoryInput) ? $subCategoryInput : [$subCategoryInput];
        $subCategorySlugs = array_values(array_unique(array_filter(
            array_map(static fn (mixed $slug): string => trim((string) $slug), $subCategorySlugs),
            static fn (string $slug): bool => $slug !== ''
        )));
        $page = max(1, (int) $request->integer('page', 1));
        $data = $pageData->getCategoryHubData($slug, $subCategorySlugs, $locale, $page);

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
