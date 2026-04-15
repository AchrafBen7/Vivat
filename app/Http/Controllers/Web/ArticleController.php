<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Services\PublicPageDataService;
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
            'title' => 'Toutes les actualités Vivat',
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

        return $this->renderArticlePage($article, $locale);
    }

    public function preview(Request $request, Article $article): Response
    {
        abort_unless($request->user()?->hasRole(['admin']), 403);

        $locale = content_locale($request);
        $article->loadMissing(['subCategory', 'category']);
        $previewBackHref = $this->resolvePreviewBackHref($request);
        $previewBackLabel = $this->resolvePreviewBackLabel($request, $previewBackHref);

        return $this->renderArticlePage($article, $locale, true, $previewBackHref, $previewBackLabel);
    }

    private function renderArticlePage(
        Article $article,
        string $locale,
        bool $isPreview = false,
        ?string $previewBackHref = null,
        ?string $previewBackLabel = null,
    ): Response
    {
        $article = $article->fresh(['subCategory', 'category']) ?? $article;

        // Catégorie toujours résolue via category_id pour cohérence avec la DB
        $category = $article->category_id ? Category::find($article->category_id) : null;

        $relatedArticles = app(PublicPageDataService::class)->getRelatedArticlesData(
            $article,
            $locale,
            self::RELATED_ARTICLES_TARGET,
        );

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
                'has_generated_cover' => ! empty($article->cover_image_url),
                'cover_status_label' => ! empty($article->cover_image_url)
                    ? 'Cover IA générée'
                    : 'Fallback visuel affiché',
                'cover_video_url' => $article->cover_video_url,
                'is_preview' => $isPreview,
                'preview_context' => $isPreview ? 'admin' : null,
                'preview_back_href' => $isPreview ? ($previewBackHref ?: \App\Filament\Resources\Articles\ArticleResource::getUrl()) : null,
                'preview_back_label' => $isPreview ? ($previewBackLabel ?: "Retour à l'administration") : null,
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
            'hide_cta_section' => $isPreview,
            'hide_footer'      => $isPreview,
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
            || (is_string($cover) && ! str_starts_with($cover, 'http') && ! str_starts_with($cover, '/uploads/'))) {
            return vivat_category_fallback_image($category?->slug, 800, 450, (string) $article->id, 'cover');
        }

        return $cover;
    }

    private function resolvePreviewBackHref(Request $request): ?string
    {
        $back = $request->query('back');

        if (! is_string($back) || trim($back) === '') {
            return null;
        }

        $back = trim($back);

        if (str_starts_with($back, '/')) {
            return $back;
        }

        $parsedBack = parse_url($back);
        if (! is_array($parsedBack)) {
            return null;
        }

        $backHost = $parsedBack['host'] ?? null;
        $requestHost = $request->getHost();

        if ($backHost !== null && $requestHost !== '' && ! hash_equals((string) $backHost, (string) $requestHost)) {
            return null;
        }

        $path = $parsedBack['path'] ?? '';
        if (! is_string($path) || $path === '') {
            return null;
        }

        $query = isset($parsedBack['query']) && $parsedBack['query'] !== '' ? '?'.$parsedBack['query'] : '';
        $fragment = isset($parsedBack['fragment']) && $parsedBack['fragment'] !== '' ? '#'.$parsedBack['fragment'] : '';

        return $path.$query.$fragment;
    }

    private function resolvePreviewBackLabel(Request $request, ?string $previewBackHref): ?string
    {
        $label = $request->query('back_label');

        if (is_string($label) && trim($label) !== '') {
            return $label;
        }

        if ($previewBackHref === \App\Filament\Pages\PipelineArticles::getUrl()) {
            return 'Retour à Brouillons AI';
        }

        return null;
    }
}
