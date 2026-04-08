<?php

namespace App\Services;

use Illuminate\Support\Str;

class ArticleContentProcessor
{
    private const WORDS_PER_MINUTE = 200;
    private const ALLOWED_HTML_TAGS = '<p><h2><h3><ul><ol><li><strong><em><a><blockquote><br>';

    public function sanitizeContent(string $text): string
    {
        $text = $this->normalizeTypography($text);
        $text = trim(strip_tags($text));
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim((string) $text);
    }

    public function sanitizeHtmlContent(string $html): string
    {
        $html = $this->normalizeTypography($html);
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        if (! class_exists(\DOMDocument::class)) {
            return trim(strip_tags($html, self::ALLOWED_HTML_TAGS));
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $wrapperId = 'vivat-sanitizer-root';
        $fragment = '<?xml encoding="UTF-8"><div id="'.$wrapperId.'">'.$html.'</div>';
        $document->loadHTML($fragment, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById($wrapperId);

        if (! $root instanceof \DOMElement) {
            return trim(strip_tags($html, self::ALLOWED_HTML_TAGS));
        }

        $this->sanitizeNode($root);

        $cleaned = '';

        foreach ($root->childNodes as $child) {
            $cleaned .= $document->saveHTML($child);
        }

        return trim($cleaned);
    }

    private function sanitizeNode(\DOMNode $node): void
    {
        if ($node instanceof \DOMElement) {
            $tagName = strtolower($node->tagName);
            $allowedTags = ['div', 'p', 'h2', 'h3', 'ul', 'ol', 'li', 'strong', 'em', 'a', 'blockquote', 'br'];
            $blockedTags = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math'];

            if (in_array($tagName, $blockedTags, true)) {
                $node->parentNode?->removeChild($node);

                return;
            }

            if (! in_array($tagName, $allowedTags, true)) {
                $this->unwrapNode($node);

                return;
            }

            $this->sanitizeAttributes($node);
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->sanitizeNode($child);
        }
    }

    private function sanitizeAttributes(\DOMElement $node): void
    {
        $tagName = strtolower($node->tagName);

        foreach (iterator_to_array($node->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);

            if (str_starts_with($name, 'on') || in_array($name, ['style', 'class', 'id', 'target'], true)) {
                $node->removeAttribute($attribute->nodeName);
                continue;
            }

            if ($tagName !== 'a' || $name !== 'href') {
                $node->removeAttribute($attribute->nodeName);
                continue;
            }

            $href = trim((string) $attribute->nodeValue);
            $isSafeHref = str_starts_with($href, 'http://')
                || str_starts_with($href, 'https://')
                || str_starts_with($href, 'mailto:')
                || str_starts_with($href, '/')
                || str_starts_with($href, '#');

            if (! $isSafeHref) {
                $node->removeAttribute('href');
                continue;
            }

            $node->setAttribute('rel', 'nofollow noopener noreferrer');
        }
    }

    private function unwrapNode(\DOMElement $node): void
    {
        $parent = $node->parentNode;

        if (! $parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    private function normalizeTypography(string $text): string
    {
        $text = str_replace(["\xe2\x80\x94", "\xe2\x80\x93"], ['&mdash;', '&mdash;'], $text);
        $text = preg_replace('/[\x{201C}\x{201D}]/u', '"', $text);
        $text = preg_replace('/[\x{2018}\x{2019}]/u', "'", $text);

        return (string) $text;
    }

    public function sanitizeMetaText(?string $text, int $limit = 190): string
    {
        $cleaned = $this->sanitizeContent((string) $text);
        $cleaned = trim(strip_tags(html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $cleaned = preg_replace('/\s+/u', ' ', $cleaned ?? '');

        return Str::limit(trim((string) $cleaned), $limit, '...');
    }

    public function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $minutes = (int) ceil($wordCount / self::WORDS_PER_MINUTE);

        return max(1, min(60, $minutes));
    }

    /**
     * @param  array<int, string>  $keywords
     */
    public function assessQuality(string $title, string $content, array $keywords): int
    {
        $score = 0;

        if (mb_strlen($title) >= 30 && mb_strlen($title) <= 70) {
            $score += 20;
        }

        $wordCount = str_word_count(strip_tags($content));
        if ($wordCount >= 800) {
            $score += 30;
        } elseif ($wordCount >= 500) {
            $score += 20;
        }

        if (preg_match_all('/<h[23]>/i', $content) >= 2) {
            $score += 25;
        }

        if (count($keywords) >= 3) {
            $score += 25;
        }

        return min(100, $score);
    }
}
