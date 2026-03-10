<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArticleController extends Controller
{
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
                'published_at_iso' => $article->published_at?->toIso8601String(),
                'cover_image_url' => $article->cover_image_url,
                'cover_video_url' => $article->cover_video_url,
                'category' => $category ? [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ] : null,
            ],
        ];

        $articleUrl = url('/articles/'.$article->slug);
        $ogImage = $article->cover_image_url
            ? (str_starts_with($article->cover_image_url, 'http') ? $article->cover_image_url : url($article->cover_image_url))
            : null;

        $content = render_php_view('site.article', $data);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'content_locale' => $locale,
            'title' => $article->meta_title ?: $article->title,
            'meta_description' => $article->meta_description ?: $article->excerpt,
            'canonical_url' => $articleUrl,
            'og_image' => $ogImage,
            'og_article' => true,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
