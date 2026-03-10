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
     * @param  string|null  $articleType  hot_news | long_form | standard — adapte ton et longueur
     * @param  int|null  $minWords  Longueur min cible (sinon depuis config/template)
     * @param  int|null  $maxWords  Longueur max cible
     * @param  string|null  $contextPriority  Contexte pour l'IA (ex: "Sur 50 articles, 10 sur ce sujet → priorité tendance")
     */
    public function generate(
        array $itemIds,
        ?string $categoryId = null,
        ?string $customPrompt = null,
        ?string $articleType = null,
        ?int $minWords = null,
        ?int $maxWords = null,
        ?string $contextPriority = null
    ): Article {
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

        $systemPrompt = $this->buildSystemPrompt($template, $articleType, $minWords, $maxWords);
        $userPrompt = $this->buildUserPrompt($items, $customPrompt, $contextPriority);

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
            'language' => 'fr',
            'cluster_id' => null,
            'reading_time' => $readingTime,
            'status' => 'draft',
            'article_type' => $articleType,
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

    private function buildSystemPrompt(
        ?CategoryTemplate $template,
        ?string $articleType = null,
        ?int $minWordsOverride = null,
        ?int $maxWordsOverride = null
    ): string {
        $articleTypesConfig = config('selection.article_types', []);
        $typeConfig = $articleType && isset($articleTypesConfig[$articleType])
            ? $articleTypesConfig[$articleType]
            : ($articleTypesConfig['standard'] ?? []);

        $minWords = $minWordsOverride ?? $template?->min_word_count ?? $typeConfig['min_words'] ?? 900;
        $maxWords = $maxWordsOverride ?? $template?->max_word_count ?? $typeConfig['max_words'] ?? 2000;
        $tone = $template?->tone ?? $typeConfig['tone'] ?? 'professionnel et accessible';
        $structure = $template?->structure ?? 'standard';
        $styleNotes = $template?->style_notes ?? '';
        $seoRules = $template?->seo_rules ?? '';

        $typeInstruction = '';
        if ($articleType === 'hot_news') {
            $typeInstruction = "\nTYPE D'ARTICLE : Brève / actualité chaude. Style percutant, direct, factuel. Titre accrocheur. Pas de développement long.";
        } elseif ($articleType === 'long_form') {
            $typeInstruction = "\nTYPE D'ARTICLE : Article de fond. Approfondi, analytique, bien structuré avec sous-parties. Contexte et mise en perspective.";
        }

        return <<<PROMPT
Tu es un rédacteur expert en contenu SEO. Génère un article de synthèse 100% original à partir des sources fournies.

RÈGLES :
- Ton : {$tone}. Structure : {$structure}.
- Longueur OBLIGATOIRE : entre {$minWords} et {$maxWords} mots (respecte cette fourchette).
- Contenu HTML avec h2/h3 bien structurés. Chaque section apporte de la valeur.
- Le titre doit contenir le mot-clé principal et être accrocheur (50-65 caractères idéal).
- Le premier paragraphe doit contenir le mot-clé principal naturellement.
- Utiliser les mots-clés SEO fournis naturellement dans le texte (densité 1-2%).
- meta_title : 50-60 caractères, contient le mot-clé principal.
- meta_description : 150-160 caractères, incitative au clic.
- keywords : mots-clés longue traîne et spécifiques (pas de termes génériques).
{$typeInstruction}

RÉPONSE UNIQUEMENT en JSON :
{
  "title": "...",
  "excerpt": "...",
  "content": "<h2>...</h2><p>...</p>...",
  "meta_title": "...",
  "meta_description": "...",
  "keywords": ["mot-clé 1", "mot-clé 2", ...]
}
{$styleNotes}
{$seoRules}
PROMPT;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, RssItem>  $items
     */
    private function buildUserPrompt($items, ?string $customPrompt, ?string $contextPriority = null): string
    {
        $parts = [];
        $allSeoKeywords = [];

        foreach ($items as $item) {
            $enriched = $item->enrichedItem;
            $sourceName = $item->rssFeed?->source?->name ?? 'Source inconnue';

            $seoKw = $enriched->seo_keywords ?? [];
            $allSeoKeywords = array_merge($allSeoKeywords, $seoKw);

            $part = "## Source : {$item->title} ({$sourceName})";
            $part .= "\nURL : {$item->url}";
            $part .= "\nSujet principal : " . ($enriched->primary_topic ?? 'Non défini');
            $part .= "\nLead : " . ($enriched->lead ?? '');
            $part .= "\nPoints clés : " . json_encode($enriched->key_points ?? []);
            if (! empty($seoKw)) {
                $part .= "\nMots-clés SEO identifiés : " . implode(', ', $seoKw);
            }
            $part .= "\nTexte extrait : " . Str::limit($enriched->extracted_text ?? '', 2000);
            $parts[] = $part;
        }

        $sources = implode("\n\n---\n\n", $parts);

        $contextBlock = '';
        if ($contextPriority !== null && $contextPriority !== '') {
            $contextBlock = "\n\n## Contexte de priorité (base-toi sur ceci pour le choix éditorial) :\n"
                . $contextPriority
                . "\n\nCe sujet est prioritaire ; l'article doit refléter cette importance.";
        }

        $uniqueKeywords = array_unique($allSeoKeywords);
        $seoSection = '';
        if (! empty($uniqueKeywords)) {
            $seoSection = "\n\n## Mots-clés SEO à intégrer (par ordre de priorité) :\n"
                . implode(', ', array_slice($uniqueKeywords, 0, 15))
                . "\n\nUtilise ces mots-clés naturellement dans le titre, les sous-titres et le corps du texte.";
        }

        $custom = $customPrompt ? "\n\n## Instructions supplémentaires :\n" . $customPrompt : '';

        return "# Sources à synthétiser ({$items->count()} articles de {$items->pluck('rssFeed.source.name')->filter()->unique()->implode(', ')})\n\n"
            . $sources
            . $contextBlock
            . $seoSection
            . $custom;
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
        $text = str_replace(["\xe2\x80\x94", "\xe2\x80\x93"], ['&mdash;', '&mdash;'], $text);
        $text = preg_replace('/[\x{201C}\x{201D}]/u', '"', $text);
        $text = preg_replace('/[\x{2018}\x{2019}]/u', "'", $text);
        return trim($text);
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
