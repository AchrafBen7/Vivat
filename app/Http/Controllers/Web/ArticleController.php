<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArticleController extends Controller
{
    private const RELATED_ARTICLES_TARGET = 4;

    public function index(Request $request): Response
    {
        $locale = content_locale($request);
        $data = app(\App\Services\PublicPageDataService::class)->getArticlesIndexData($locale);
        $content = render_php_view('site.articles_index', $data);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => 'Toutes les actualités — Vivat',
            'meta_description' => 'Découvrez tous les articles et actualités Vivat. Parcourez nos derniers contenus par rubrique.',
            'canonical_url' => url('/articles'),
            'trim_main_bottom' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function show(Request $request, string $slug): Response
    {
        $locale = content_locale($request);
        $article = Article::published()
            ->forLocale($locale)
            ->with(['subCategory'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Catégorie toujours résolue via category_id pour cohérence avec la DB
        $category = $article->category_id ? Category::find($article->category_id) : null;

        $primaryRelated = Article::published()
            ->forLocale($locale)
            ->with('category')
            ->where('id', '!=', $article->id)
            ->when($article->category_id, fn ($q) => $q->where('category_id', $article->category_id))
            ->orderByDesc('published_at')
            ->limit(self::RELATED_ARTICLES_TARGET)
            ->get();

        $selectedIds = $primaryRelated->pluck('id')->prepend($article->id)->all();
        $missingCount = max(0, self::RELATED_ARTICLES_TARGET - $primaryRelated->count());

        $fallbackRelated = collect();
        if ($missingCount > 0) {
            $fallbackRelated = Article::published()
                ->forLocale($locale)
                ->with('category')
                ->whereNotIn('id', $selectedIds)
                ->orderByDesc('published_at')
                ->limit($missingCount)
                ->get();
        }

        $relatedArticles = $primaryRelated
            ->concat($fallbackRelated)
            ->take(self::RELATED_ARTICLES_TARGET)
            ->map(fn (Article $a) => $this->mapRelatedArticle($a, $category, $locale))
            ->values()
            ->all();

        $data = [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'meta_title' => $article->meta_title,
                'meta_description' => $article->meta_description,
                'reading_time' => $article->reading_time,
                'published_at' => $article->published_at?->format('d/m/Y H:i'),
                'published_at_display' => $article->published_at?->locale('fr')->isoFormat('D MMMM YYYY'),
                'published_at_iso' => $article->published_at?->toIso8601String(),
                'cover_image_url' => $this->articleCoverOrFallback($article, $category),
                'cover_video_url' => $article->cover_video_url,
                'category' => $category ? [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ] : null,
            ],
            'related_articles' => $relatedArticles,
        ];

        $articleUrl = url('/articles/'.$article->slug);
        $coverUrl = $this->articleCoverOrFallback($article, $category);
        $ogImage = $coverUrl
            ? (str_starts_with($coverUrl, 'http') ? $coverUrl : url($coverUrl))
            : null;

        $content = render_php_view('site.article', $data);
        $html = render_php_view('site.layout', [
            'content'          => $content,
            'content_locale'   => $locale,
            'title'            => $article->meta_title ?: $article->title,
            'meta_description' => $article->meta_description ?: $article->excerpt,
            'canonical_url'    => $articleUrl,
            'og_image'         => $ogImage,
            'og_article'       => true,
            'trim_main_bottom' => true,
            'compact_cta_spacing' => true,
            'json_ld'          => [
                '@context'         => 'https://schema.org',
                '@type'            => 'Article',
                'headline'         => $article->meta_title ?: $article->title,
                'description'      => $article->meta_description ?: $article->excerpt,
                'url'              => $articleUrl,
                'datePublished'    => $article->published_at?->toIso8601String(),
                'dateModified'     => $article->updated_at?->toIso8601String(),
                'image'            => $ogImage,
                'inLanguage'       => $locale === 'nl' ? 'nl-BE' : 'fr-BE',
                'publisher'        => [
                    '@type' => 'Organization',
                    'name'  => 'Vivat',
                    'url'   => url('/'),
                ],
                'keywords' => is_array($article->keywords) ? implode(', ', $article->keywords) : ($article->keywords ?? ''),
            ],
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function articleCoverOrFallback(Article $article, ?Category $category): string
    {
        $cover = $article->cover_image_url;
        if (empty($cover)
            || (is_string($cover) && stripos($cover, 'picsum') !== false)
            || (is_string($cover) && ! str_starts_with($cover, 'http'))) {
            return vivat_category_fallback_image($category?->slug, 800, 450, (string) $article->id, 'cover');
        }

        return $cover;
    }

    private function mapRelatedArticle(Article $article, ?Category $currentCategory, string $locale): array
    {
        return [
            'title' => $article->title,
            'slug' => $article->slug,
            'reading_time' => $article->reading_time,
            'category' => $article->category?->name ?? '',
            'published_at_display' => $article->published_at?->locale($locale)->isoFormat('D MMMM YYYY'),
            'image' => $this->articleCoverOrFallback($article, $article->category),
            'fallback' => vivat_category_fallback_image(
                $article->category?->slug ?? $currentCategory?->slug,
                760,
                520,
                (string) $article->id,
                'also'
            ),
        ];
    }
}
