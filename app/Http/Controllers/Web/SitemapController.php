<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $articles = Article::published()
            ->select(['slug', 'updated_at', 'published_at', 'language'])
            ->orderByDesc('published_at')
            ->get();

        $categories = Category::select(['slug', 'created_at'])->get();

        $staticPages = [
            ['url' => url('/'),                       'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => url('/articles'),               'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => url('/search'),                 'priority' => '0.6', 'changefreq' => 'weekly'],
            ['url' => url('/a-propos'),               'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => url('/faq'),                    'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => url('/contact'),                'priority' => '0.4', 'changefreq' => 'monthly'],
            ['url' => url('/mentions-legales'),       'priority' => '0.3', 'changefreq' => 'yearly'],
            ['url' => url('/politique-confidentialite'), 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['url' => url('/politique-cookies'),      'priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        $xml = $this->buildXml($staticPages, $categories, $articles);

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function buildXml(array $staticPages, $categories, $articles): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($staticPages as $page) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . htmlspecialchars($page['url']) . '</loc>';
            $lines[] = '    <changefreq>' . $page['changefreq'] . '</changefreq>';
            $lines[] = '    <priority>' . $page['priority'] . '</priority>';
            $lines[] = '  </url>';
        }

        foreach ($categories as $category) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . htmlspecialchars(url('/categories/' . $category->slug)) . '</loc>';
            $lines[] = '    <lastmod>' . $category->created_at->toAtomString() . '</lastmod>';
            $lines[] = '    <changefreq>weekly</changefreq>';
            $lines[] = '    <priority>0.7</priority>';
            $lines[] = '  </url>';
        }

        foreach ($articles as $article) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . htmlspecialchars(url('/articles/' . $article->slug)) . '</loc>';
            $lines[] = '    <lastmod>' . $article->updated_at->toAtomString() . '</lastmod>';
            $lines[] = '    <changefreq>monthly</changefreq>';
            $lines[] = '    <priority>0.8</priority>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines);
    }
}
