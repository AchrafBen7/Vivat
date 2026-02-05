<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleSource;
use App\Models\CategoryTemplate;
use App\Models\RssItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ArticleGeneratorService
{
    private const WORDS_PER_MINUTE = 200;

    public function __construct() {}

    /**
     * Génère un article à partir d'items RSS enrichis.
     *
     * @param  array<int, string>  $itemIds  UUID des RssItem (enriched)
     * @param  string|null  $categoryId  UUID catégorie optionnelle
     * @param  string|null  $customPrompt  Instructions supplémentaires
     */
    public function generate(array $itemIds, ?string $categoryId = null, ?string $customPrompt = null): Article
    {
        $items = RssItem::query()
            ->with(['enrichedItem', 'rssFeed.source', 'category'])
            ->whereIn('id', $itemIds)
            ->get();

        if ($items->isEmpty()) {
            throw new \InvalidArgumentException('Aucun item enrichi trouvé pour les IDs fournis.');
        }

        foreach ($items as $item) {
            if ($item->enrichedItem === null) {
                throw new \InvalidArgumentException("L'item {$item->id} n'est pas enrichi.");
            }
        }

        $template = null;
        if ($categoryId) {
            $template = CategoryTemplate::where('category_id', $categoryId)->first();
        }
        $categoryId = $categoryId ?? $items->first()->category_id;

        $systemPrompt = $this->buildSystemPrompt($template);
        $userPrompt = $this->buildUserPrompt($items, $customPrompt);

        $json = $this->callOpenAI($systemPrompt, $userPrompt);
        $content = $this->sanitizeContent($json['content'] ?? $json['body'] ?? '');
        $title = $this->sanitizeContent($json['title'] ?? 'Sans titre');
        $excerpt = $this->sanitizeContent($json['excerpt'] ?? Str::limit(strip_tags($content), 200));
        $metaTitle = $this->sanitizeContent($json['meta_title'] ?? Str::limit($title, 60));
        $metaDescription = $this->sanitizeContent($json['meta_description'] ?? Str::limit($excerpt, 160));
        $keywords = isset($json['keywords']) && is_array($json['keywords'])
            ? $json['keywords']
            : [];

        $readingTime = $this->calculateReadingTime($content);
        $qualityScore = $this->assessQuality($title, $content, $keywords);

        $slug = Str::slug($title) . '-' . Str::lower(Str::random(6));

        $article = Article::create([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'keywords' => $keywords,
            'category_id' => $categoryId,
            'cluster_id' => null,
            'reading_time' => $readingTime,
            'status' => 'draft',
            'quality_score' => $qualityScore,
        ]);

        foreach ($items as $item) {
            ArticleSource::create([
                'article_id' => $article->id,
                'rss_item_id' => $item->id,
                'source_id' => $item->rssFeed?->source_id,
                'url' => $item->url,
            ]);
            $item->update(['status' => 'used']);
        }

        return $article->load('articleSources');
    }

    private function buildSystemPrompt(?CategoryTemplate $template): string
    {
        $tone = $template?->tone ?? 'professional';
        $structure = $template?->structure ?? 'standard';
        $minWords = $template?->min_word_count ?? 900;
        $maxWords = $template?->max_word_count ?? 2000;
        $styleNotes = $template?->style_notes ?? '';
        $seoRules = $template?->seo_rules ?? '';

        return <<<PROMPT
Tu es un rédacteur expert. Génère un article de synthèse original à partir des sources fournies.
- Ton : {$tone}. Structure : {$structure}.
- Longueur : entre {$minWords} et {$maxWords} mots.
- Réponds UNIQUEMENT en JSON avec les clés : title, excerpt, content (HTML avec h2/h3), meta_title, meta_description, keywords (tableau de chaînes).
{$styleNotes}
{$seoRules}
PROMPT;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, RssItem>  $items
     */
    private function buildUserPrompt($items, ?string $customPrompt): string
    {
        $parts = [];
        foreach ($items as $item) {
            $enriched = $item->enrichedItem;
            $parts[] = "## Source : {$item->title}\nURL : {$item->url}\nLead : " . ($enriched->lead ?? '') . "\nPoints clés : " . json_encode($enriched->key_points ?? []) . "\nTexte extrait (extrait) : " . Str::limit($enriched->extracted_text ?? '', 1500);
        }
        $sources = implode("\n\n", $parts);
        $custom = $customPrompt ? "\n\nInstructions supplémentaires : " . $customPrompt : '';
        return "Sources à synthétiser :\n\n{$sources}{$custom}";
    }

    /**
     * @return array<string, mixed>
     */
    private function callOpenAI(string $systemPrompt, string $userPrompt): array
    {
        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            throw new \RuntimeException('OPENAI_API_KEY non configurée.');
        }

        $response = Http::withToken($apiKey)
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
                'max_tokens' => config('services.openai.max_tokens', 4000),
            ]);

        if ($response->failed()) {
            $body = $response->json();
            $message = $body['error']['message'] ?? $response->body();
            throw new \RuntimeException("OpenAI API error: {$message}");
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content)) {
            throw new \RuntimeException('Réponse OpenAI invalide.');
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('JSON invalide dans la réponse OpenAI.');
        }

        return $decoded;
    }

    private function sanitizeContent(string $text): string
    {
        $text = str_replace(['—', '–'], ['—', '—'], $text);
        $text = preg_replace('/[""]/u', '"', $text ?? '');
        $text = preg_replace('/['']/u', "'", $text ?? '');
        return trim($text ?? '');
    }

    private function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $minutes = (int) ceil($wordCount / self::WORDS_PER_MINUTE);
        return max(1, min(60, $minutes));
    }

    /**
     * @param  array<int, string>  $keywords
     */
    private function assessQuality(string $title, string $content, array $keywords): int
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
