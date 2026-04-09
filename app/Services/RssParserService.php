<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

class RssParserService
{
    /**
     * Parse un flux RSS 2.0 ou Atom et retourne une liste d'items normalisés.
     *
     * @return array<int, array{title: string, link: string, description: string, pubDate: ?string, guid: ?string}>
     */
    public function parse(string $xml): array
    {
        $xml = trim($xml);
        if ($xml === '') {
            return [];
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        if (! @$dom->loadXML($xml)) {
            libxml_clear_errors();
            return [];
        }
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
        $xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $xpath->registerNamespace('content', 'http://purl.org/rss/1.0/modules/content/');

        // RSS 2.0 : /rss/channel/item
        $rssItems = $xpath->query('//channel/item');
        if ($rssItems->length > 0) {
            return $this->parseRssItems($xpath, $rssItems);
        }

        // Atom : /feed/entry
        $atomEntries = $xpath->query('//atom:entry');
        if ($atomEntries->length > 0) {
            return $this->parseAtomEntries($xpath, $atomEntries);
        }

        return [];
    }

    /**
     * @param \DOMNodeList<\DOMNode> $items
     * @return array<int, array{title: string, link: string, description: string, pubDate: ?string, guid: ?string}>
     */
    private function parseRssItems(DOMXPath $xpath, \DOMNodeList $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $title = $this->extractTag($item, 'title');
            $link = $this->extractTag($item, 'link');
            $description = $this->extractTag($item, 'description')
                ?? $this->extractTag($item, 'content:encoded');
            $pubDate = $this->extractTag($item, 'pubDate');
            $guid = $this->extractTag($item, 'guid');

            if ($title !== null && $link !== null) {
                $result[] = [
                    'title' => $title,
                    'link' => $link,
                    'description' => $description ?? '',
                    'pubDate' => $this->normalizePubDate($pubDate),
                    'guid' => $guid,
                ];
            }
        }
        return $result;
    }

    /**
     * @param \DOMNodeList<\DOMNode> $entries
     * @return array<int, array{title: string, link: string, description: string, pubDate: ?string, guid: ?string}>
     */
    private function parseAtomEntries(DOMXPath $xpath, \DOMNodeList $entries): array
    {
        $result = [];
        foreach ($entries as $entry) {
            $title = $this->extractTag($entry, 'title', 'http://www.w3.org/2005/Atom');
            $link = $this->extractAtomLink($entry);
            $description = $this->extractTag($entry, 'summary', 'http://www.w3.org/2005/Atom')
                ?? $this->extractTag($entry, 'content', 'http://www.w3.org/2005/Atom');
            $updated = $this->extractTag($entry, 'updated', 'http://www.w3.org/2005/Atom');
            $id = $this->extractTag($entry, 'id', 'http://www.w3.org/2005/Atom');

            if ($title !== null && $link !== null) {
                $result[] = [
                    'title' => $title,
                    'link' => $link,
                    'description' => $description ?? '',
                    'pubDate' => $this->normalizePubDate($updated),
                    'guid' => $id,
                ];
            }
        }
        return $result;
    }

    private function extractTag(\DOMNode $parent, string $tagName, ?string $ns = null): ?string
    {
        $node = $ns
            ? $parent->getElementsByTagNameNS($ns, $tagName)->item(0)
            : $parent->getElementsByTagName($tagName)->item(0);
        if (! $node || ! $node->firstChild) {
            return null;
        }
        $text = $node->textContent;
        return $text !== null ? trim($text) : null;
    }

    private function extractAtomLink(\DOMNode $entry): ?string
    {
        $nodes = $entry->getElementsByTagNameNS('http://www.w3.org/2005/Atom', 'link');
        foreach ($nodes as $node) {
            $rel = $node->getAttribute('rel') ?: 'alternate';
            if ($rel === 'alternate' || $rel === '') {
                $href = $node->getAttribute('href');
                if ($href !== '') {
                    return $href;
                }
            }
        }
        return null;
    }

    /**
     * Génère un hash de déduplication (32 caractères) à partir de guid ou link+title.
     */
    public function generateDedupHash(?string $guid, string $link, string $title): string
    {
        $raw = $guid !== null && $guid !== '' ? $guid : ($link . $title);
        return substr(hash('sha256', $raw), 0, 32);
    }

    public function normalizePubDate(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $normalized = mb_strtolower($value, 'UTF-8');
        $normalized = str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ç'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c'],
            $normalized
        );

        $map = [
            'lun.' => 'Mon', 'lundi' => 'Mon',
            'mar.' => 'Tue', 'mardi' => 'Tue',
            'mer.' => 'Wed', 'mercredi' => 'Wed',
            'jeu.' => 'Thu', 'jeudi' => 'Thu',
            'ven.' => 'Fri', 'vendredi' => 'Fri',
            'sam.' => 'Sat', 'samedi' => 'Sat',
            'dim.' => 'Sun', 'dimanche' => 'Sun',
            'janv.' => 'Jan', 'janvier' => 'Jan',
            'fevr.' => 'Feb', 'fevrier' => 'Feb',
            'févr.' => 'Feb', 'fev.' => 'Feb',
            'mars' => 'Mar',
            'avr.' => 'Apr', 'avril' => 'Apr',
            'mai' => 'May',
            'juin' => 'Jun',
            'juil.' => 'Jul', 'juillet' => 'Jul',
            'aout' => 'Aug', 'août' => 'Aug',
            'sept.' => 'Sep', 'septembre' => 'Sep',
            'oct.' => 'Oct', 'octobre' => 'Oct',
            'nov.' => 'Nov', 'novembre' => 'Nov',
            'dec.' => 'Dec', 'déc.' => 'Dec', 'decembre' => 'Dec', 'décembre' => 'Dec',
        ];

        $normalized = strtr($normalized, $map);
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }
}
