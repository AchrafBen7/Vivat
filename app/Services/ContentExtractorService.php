<?php

namespace App\Services;

use DOMDocument;
use Illuminate\Support\Facades\Http;

class ContentExtractorService
{
    private const MIN_PARAGRAPHS = 5;

    private const MIN_WORDS = 300;

    private const USER_AGENT = 'Mozilla/5.0 (compatible; ContentBot/1.0; +https://vivat.example/bot)';

    /**
     * Tags à retirer du HTML.
     */
    private const REMOVE_TAGS = ['script', 'style', 'nav', 'header', 'footer', 'iframe'];

    /**
     * Extrait le contenu d'une page web depuis son URL.
     *
     * @return array{title: string, headings: array<int, string>, text: string, html: string, internal_links: array<int, string>, word_count: int, deep_scraped?: bool}|null
     */
    public function extract(string $url): ?array
    {
        $response = Http::timeout(25)
            ->withHeaders(['User-Agent' => self::USER_AGENT])
            ->get($url);

        if ($response->failed()) {
            return null;
        }

        $html = $response->body();
        $baseUrl = $this->parseBaseUrl($url);

        $dom = $this->loadHtml($html);
        if ($dom === null) {
            return null;
        }

        $this->removeTags($dom, self::REMOVE_TAGS);

        $title = $this->extractTitle($dom);
        $headings = $this->extractHeadings($dom);
        $mainHtml = $this->extractMainContent($dom);
        $text = $this->htmlToText($mainHtml);
        $wordCount = str_word_count($text);
        $internalLinks = $this->extractInternalLinks($dom, $baseUrl);

        $deepScraped = false;
        if ($this->needsDeepScrape($mainHtml, $wordCount)) {
            $mainHtml = $this->extractLargestTextBlock($dom);
            $text = $this->htmlToText($mainHtml);
            $wordCount = str_word_count($text);
            $deepScraped = true;
        }

        return [
            'title' => $title,
            'headings' => $headings,
            'text' => $text,
            'html' => $mainHtml,
            'internal_links' => $internalLinks,
            'word_count' => $wordCount,
            'deep_scraped' => $deepScraped,
        ];
    }

    private function loadHtml(string $html): ?DOMDocument
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        if (! @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            libxml_clear_errors();
            return null;
        }
        libxml_clear_errors();
        return $dom;
    }

    private function parseBaseUrl(string $url): string
    {
        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        return $scheme . '://' . $host;
    }

    private function removeTags(DOMDocument $dom, array $tags): void
    {
        foreach ($tags as $tagName) {
            $nodes = $dom->getElementsByTagName($tagName);
            $toRemove = [];
            foreach ($nodes as $node) {
                $toRemove[] = $node;
            }
            foreach ($toRemove as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    private function extractTitle(DOMDocument $dom): string
    {
        $h1 = $dom->getElementsByTagName('h1')->item(0);
        if ($h1 !== null && $h1->textContent !== null) {
            return trim($h1->textContent);
        }
        $title = $dom->getElementsByTagName('title')->item(0);
        if ($title !== null && $title->textContent !== null) {
            return trim($title->textContent);
        }
        return '';
    }

    /**
     * @return array<int, string>
     */
    private function extractHeadings(DOMDocument $dom): array
    {
        $headings = [];
        foreach (['h1', 'h2', 'h3'] as $tag) {
            $nodes = $dom->getElementsByTagName($tag);
            foreach ($nodes as $node) {
                if ($node->textContent !== null) {
                    $headings[] = trim($node->textContent);
                }
            }
        }
        return $headings;
    }

    private function extractMainContent(DOMDocument $dom): string
    {
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body === null) {
            return '';
        }
        $candidates = [
            $dom->getElementsByTagName('article')->item(0),
            $dom->getElementsByTagName('main')->item(0),
        ];
        foreach ($candidates as $node) {
            if ($node !== null) {
                $html = $dom->saveHTML($node);
                if (str_word_count(strip_tags($html ?? '')) >= 50) {
                    return $html ?? '';
                }
            }
        }
        return $dom->saveHTML($body) ?: '';
    }

    private function extractLargestTextBlock(DOMDocument $dom): string
    {
        $paragraphs = $dom->getElementsByTagName('p');
        $best = '';
        $bestCount = 0;
        foreach ($paragraphs as $p) {
            $parent = $p->parentNode;
            if ($parent === null) {
                continue;
            }
            $html = $dom->saveHTML($parent);
            $wc = str_word_count(strip_tags($html ?? ''));
            if ($wc > $bestCount) {
                $bestCount = $wc;
                $best = $html ?? '';
            }
        }
        if ($best !== '') {
            return $best;
        }
        $body = $dom->getElementsByTagName('body')->item(0);
        return $body !== null ? ($dom->saveHTML($body) ?: '') : '';
    }

    private function needsDeepScrape(string $html, int $wordCount): bool
    {
        $paragraphs = substr_count(strtolower($html), '<p');
        return $paragraphs < self::MIN_PARAGRAPHS || $wordCount < self::MIN_WORDS;
    }

    private function htmlToText(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text ?? '');
        return trim($text ?? '');
    }

    /**
     * @return array<int, string>
     */
    private function extractInternalLinks(DOMDocument $dom, string $baseUrl): array
    {
        $links = [];
        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $a) {
            $href = $a->getAttribute('href');
            if ($href === '' || $href === '#') {
                continue;
            }
            if (str_starts_with($href, '/') || str_starts_with($href, $baseUrl)) {
                $links[] = str_starts_with($href, 'http') ? $href : $baseUrl . $href;
            }
        }
        return array_values(array_unique($links));
    }
}
