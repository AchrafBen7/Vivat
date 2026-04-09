<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Services\PublicPageDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $locale = content_locale($request);
        $q = trim((string) $request->get('q', ''));

        $data = app(PublicPageDataService::class)->getSearchData($locale, $q);
        $content = render_php_view('site.search', $data);

        $title = $q !== ''
            ? sprintf('Recherche : %s Vivat', htmlspecialchars($q))
            : 'Recherche Vivat';
        $metaDescription = $q !== ''
            ? sprintf('Résultats de recherche pour « %s » sur Vivat.', htmlspecialchars($q))
            : 'Recherchez des articles et actualités par mot-clé ou par catégorie.';

        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => $title,
            'meta_description' => $metaDescription,
            'canonical_url' => url('/search' . ($q !== '' ? '?q=' . rawurlencode($q) : '')),
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $locale = content_locale($request);
        $q = trim((string) $request->query('q', ''));
        $normalized = mb_strtolower($q);

        if (mb_strlen($q) < 2) {
            return response()->json([
                'query' => $q,
                'suggestions' => [],
            ]);
        }

        $escaped = addcslashes($q, '%_\\');

        $articleSuggestions = Article::published()
            ->forLocale($locale)
            ->with('category')
            ->where('title', 'LIKE', '%' . $escaped . '%')
            ->orderByRaw(
                'CASE
                    WHEN LOWER(title) = ? THEN 0
                    WHEN LOWER(title) LIKE ? THEN 1
                    WHEN LOWER(title) LIKE ? THEN 2
                    ELSE 3
                END',
                [$normalized, $normalized . '%', '% ' . $normalized . '%']
            )
            ->orderByDesc('published_at')
            ->limit(6)
            ->get()
            ->map(fn (Article $article) => [
                'type' => 'article',
                'label' => $article->title,
                'url' => url('/articles/' . $article->slug),
                'meta' => $article->category?->name ?: 'Article',
                'thumbnail_url' => $this->articleSuggestionThumbnail($article),
            ]);

        $categorySuggestions = Category::query()
            ->where('name', 'LIKE', '%' . $escaped . '%')
            ->orderByRaw(
                'CASE
                    WHEN LOWER(name) = ? THEN 0
                    WHEN LOWER(name) LIKE ? THEN 1
                    WHEN LOWER(name) LIKE ? THEN 2
                    ELSE 3
                END',
                [$normalized, $normalized . '%', '% ' . $normalized . '%']
            )
            ->orderBy('name')
            ->limit(4)
            ->get()
            ->map(fn (Category $category) => [
                'type' => 'category',
                'label' => $category->name,
                'url' => url('/categories/' . $category->slug),
                'meta' => 'Rubrique',
                'thumbnail_url' => null,
            ]);

        $suggestions = $categorySuggestions
            ->concat($articleSuggestions)
            ->map(function (array $item) use ($normalized) {
                $label = mb_strtolower($item['label']);
                $priority = $label === $normalized
                    ? 0
                    : (str_starts_with($label, $normalized)
                        ? 1
                        : (str_contains($label, ' ' . $normalized) ? 2 : 3));

                return $item + ['priority' => $priority];
            })
            ->sortBy([
                ['priority', 'asc'],
                ['type', 'asc'],
                ['label', 'asc'],
            ])
            ->unique(fn (array $item) => $item['type'] . '|' . mb_strtolower($item['label']))
            ->take(8)
            ->map(fn (array $item) => [
                'type' => $item['type'],
                'label' => $item['label'],
                'url' => $item['url'],
                'meta' => $item['meta'],
                'thumbnail_url' => $item['thumbnail_url'] ?? null,
            ])
            ->values();

        return response()->json([
            'query' => $q,
            'suggestions' => $suggestions,
        ]);
    }

    private function articleSuggestionThumbnail(Article $article): string
    {
        $categorySlug = $article->category?->slug;
        $cover = $article->cover_image_url;

        if (is_string($cover)
            && $cover !== ''
            && stripos($cover, 'picsum') === false
            && (str_starts_with($cover, 'http') || str_starts_with($cover, '/uploads/'))) {
            return $cover;
        }

        return vivat_category_fallback_image($categorySlug, 120, 120, (string) $article->id, 'search');
    }
}
