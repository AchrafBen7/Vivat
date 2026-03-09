<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArticleController extends Controller
{
    public function index(): Response
    {
        $data = app(\App\Services\PublicPageDataService::class)->getArticlesIndexData();
        $content = render_php_view('site.articles_index', $data);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'title' => 'Toutes les actualités — Vivat',
            'meta_description' => 'Découvrez tous les articles Vivat.',
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function show(Request $request, string $slug): Response
    {
        $article = Article::published()
            ->with(['category', 'subCategory'])
            ->where('slug', $slug)
            ->firstOrFail();

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
                'cover_image_url' => $article->cover_image_url,
                'cover_video_url' => $article->cover_video_url,
                'category' => $article->category ? [
                    'name' => $article->category->name,
                    'slug' => $article->category->slug,
                ] : null,
            ],
        ];

        $content = render_php_view('site.article', $data);
        $html = render_php_view('site.layout', [
            'content' => $content,
            'title' => $article->meta_title ?: $article->title,
            'meta_description' => $article->meta_description ?: $article->excerpt,
        ]);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
