<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicFeedController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $locale = $request->query('lang', 'fr');
        if (! in_array($locale, ['fr', 'nl'], true)) {
            $locale = 'fr';
        }

        $articles = Article::published()
            ->forLocale($locale)
            ->with('category')
            ->orderByDesc('published_at')
            ->limit(50)
            ->get();

        $xml = $this->buildRss($articles, $locale);

        return response($xml, 200, [
            'Content-Type'  => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    private function buildRss($articles, string $locale): string
    {
        $siteUrl  = rtrim(config('app.url'), '/');
        $feedUrl  = $siteUrl . '/feed.xml' . ($locale === 'nl' ? '?lang=nl' : '');
        $siteName = 'Vivat';
        $siteDesc = $locale === 'nl'
            ? 'Vivat Actualiteiten en artikelen in het Nederlands.'
            : 'Vivat Actualités et articles en français.';
        $lastBuild = $articles->first()?->published_at?->toRfc1123String()
            ?? now()->toRfc1123String();

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<rss version="2.0"',
            '  xmlns:atom="http://www.w3.org/2005/Atom"',
            '  xmlns:media="http://search.yahoo.com/mrss/"',
            '  xmlns:content="http://purl.org/rss/1.0/modules/content/">',
            '<channel>',
            '  <title>' . $this->cdata($siteName) . '</title>',
            '  <link>' . htmlspecialchars($siteUrl) . '</link>',
            '  <description>' . $this->cdata($siteDesc) . '</description>',
            '  <language>' . htmlspecialchars($locale === 'nl' ? 'nl-be' : 'fr-be') . '</language>',
            '  <lastBuildDate>' . $lastBuild . '</lastBuildDate>',
            '  <atom:link href="' . htmlspecialchars($feedUrl) . '" rel="self" type="application/rss+xml"/>',
        ];

        foreach ($articles as $article) {
            $url      = $siteUrl . '/articles/' . $article->slug;
            $pubDate  = $article->published_at?->toRfc1123String() ?? now()->toRfc1123String();
            $cover    = $this->resolveCover($article);
            $keywords = is_array($article->keywords) ? implode(', ', $article->keywords) : '';
            $catName  = $article->category?->name ?? '';

            $lines[] = '  <item>';
            $lines[] = '    <title>' . $this->cdata($article->title) . '</title>';
            $lines[] = '    <link>' . htmlspecialchars($url) . '</link>';
            $lines[] = '    <guid isPermaLink="true">' . htmlspecialchars($url) . '</guid>';
            $lines[] = '    <pubDate>' . $pubDate . '</pubDate>';
            $lines[] = '    <description>' . $this->cdata($article->excerpt ?: $article->meta_description ?: '') . '</description>';

            if ($catName !== '') {
                $lines[] = '    <category>' . $this->cdata($catName) . '</category>';
            }
            if ($keywords !== '') {
                $lines[] = '    <category>' . $this->cdata($keywords) . '</category>';
            }
            if ($cover !== '') {
                $lines[] = '    <media:content url="' . htmlspecialchars($cover) . '" medium="image"/>';
                $lines[] = '    <enclosure url="' . htmlspecialchars($cover) . '" type="image/jpeg" length="0"/>';
            }
            if (! empty($article->content)) {
                $lines[] = '    <content:encoded>' . $this->cdata($article->content) . '</content:encoded>';
            }
            $lines[] = '  </item>';
        }

        $lines[] = '</channel>';
        $lines[] = '</rss>';

        return implode("\n", $lines);
    }

    private function resolveCover(Article $article): string
    {
        $cover = $article->cover_image_url ?? '';
        if (is_string($cover)
            && $cover !== ''
            && (str_starts_with($cover, 'http') || str_starts_with($cover, '/uploads/'))
            && stripos($cover, 'picsum') === false
        ) {
            return $cover;
        }

        return '';
    }

    private function cdata(string $text): string
    {
        return '<![CDATA[' . $text . ']]>';
    }
}
