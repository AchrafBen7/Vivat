<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleSource;
use App\Models\CategoryTemplate;
use App\Models\RssItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleGeneratorService
{
    public function __construct(
        private readonly ArticlePromptBuilder $promptBuilder,
        private readonly ArticleContentProcessor $contentProcessor,
        private readonly CoverImageService $coverImageService,
        private readonly GeneratedArticlePayloadValidator $payloadValidator,
    ) {}

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
        ?string $contextPriority = null,
        ?string $clusterId = null
    ): Article {
        $items = RssItem::query()
            ->with(['enrichedItem', 'rssFeed.source', 'category'])
            ->whereIn('id', $itemIds)
            ->where('status', 'enriched')
            ->get();

        if ($items->isEmpty()) {
            throw new \InvalidArgumentException('Aucun item enrichi trouvé pour les IDs fournis.');
        }

        foreach ($items as $item) {
            if ($item->enrichedItem === null) {
                throw new \InvalidArgumentException("L'item {$item->id} n'est pas enrichi.");
            }
        }

        $alreadyLinkedItemIds = ArticleSource::query()
            ->whereIn('rss_item_id', $items->pluck('id'))
            ->whereHas('article', fn ($query) => $query->whereIn('status', ['draft', 'review', 'published']))
            ->pluck('rss_item_id')
            ->unique()
            ->values()
            ->all();

        if ($alreadyLinkedItemIds !== []) {
            throw new \RuntimeException('Une génération existe déjà pour une partie de ces sources. Vérifiez les brouillons IA avant de relancer.');
        }

        $template = null;
        if ($categoryId) {
            $template = CategoryTemplate::where('category_id', $categoryId)->first();
        }
        $categoryId = $categoryId ?? $items->first()->category_id;

        $systemPrompt = $this->promptBuilder->buildSystemPrompt($template, $articleType, $minWords, $maxWords);
        $userPrompt = $this->promptBuilder->buildUserPrompt($items, $customPrompt, $contextPriority);

        $json = $this->callOpenAI($systemPrompt, $userPrompt);
        $articlePayload = $this->payloadValidator->validateAndNormalize($json);
        $content = $articlePayload['content'];
        $title = $articlePayload['title'];
        $excerpt = $articlePayload['excerpt'];
        $metaTitle = $articlePayload['meta_title'];
        $metaDescription = $articlePayload['meta_description'];
        $keywords = $articlePayload['keywords'];

        // Injecter la section sources en bas de l'article (attribution éditoriale)
        $content = $content . $this->buildSourcesSection($items);

        $readingTime = $this->contentProcessor->calculateReadingTime($content);
        $qualityScore = $this->contentProcessor->assessQuality($title, $content, $keywords);

        $slug = Str::slug($title) . '-' . Str::lower(Str::random(6));

        $article = DB::transaction(function () use (
            $title,
            $slug,
            $excerpt,
            $content,
            $metaTitle,
            $metaDescription,
            $keywords,
            $categoryId,
            $clusterId,
            $readingTime,
            $articleType,
            $qualityScore,
            $items
        ): Article {
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
                'cluster_id' => $clusterId,
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
                    'used_at' => null,
                ]);
            }

            return $article;
        });

        if (config('services.openai.generate_cover_images', true)) {
            try {
                $coverUrl = $this->coverImageService->generate($title, $excerpt, $categoryId);
                if ($coverUrl !== null) {
                    $article->update(['cover_image_url' => $coverUrl]);
                }
            } catch (\Throwable $e) {
                Log::warning('Cover image generation failed: ' . $e->getMessage(), [
                    'article_id' => $article->id,
                ]);
            }
        }

        return $article->load('articleSources');
    }

    /**
     * Construit la section "Sources" HTML injectée en bas de chaque article généré.
     * Garantit l'attribution éditoriale et la conformité copyright.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, RssItem>  $items
     */
    private function buildSourcesSection($items): string
    {
        $sourceLines = $items->map(function (RssItem $item): string {
            $sourceName = e($item->rssFeed?->source?->name ?? 'Source externe');
            $title = e($item->title);
            $url = e($item->url);
            return "<li><a href=\"{$url}\" rel=\"nofollow noopener noreferrer\" target=\"_blank\">{$title}</a> — {$sourceName}</li>";
        })->implode("\n");

        return <<<HTML

<div class="article-sources">
<h3>Sources</h3>
<ul>
{$sourceLines}
</ul>
<p class="article-sources__disclaimer">Cet article est une synthèse éditoriale rédigée à partir des sources ci-dessus. Les informations ont été reformulées et mises en contexte par la rédaction de Vivat.</p>
</div>
HTML;
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
}
