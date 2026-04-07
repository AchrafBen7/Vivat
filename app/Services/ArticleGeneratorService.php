<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleSource;
use App\Models\CategoryTemplate;
use App\Models\RssItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        $systemPrompt = $this->buildSystemPrompt($template, $articleType, $minWords, $maxWords);
        $userPrompt = $this->buildUserPrompt($items, $customPrompt, $contextPriority);

        $json = $this->callOpenAI($systemPrompt, $userPrompt);
        $content = $this->sanitizeContent($json['content'] ?? $json['body'] ?? '');
        $title = $this->sanitizeContent($json['title'] ?? 'Sans titre');
        $excerpt = $this->sanitizeContent($json['excerpt'] ?? Str::limit(strip_tags($content), 200));
        $metaTitle = $this->sanitizeMetaText($json['meta_title'] ?? $title, 190);
        $metaDescription = $this->sanitizeMetaText($json['meta_description'] ?? $excerpt, 190);
        $keywords = isset($json['keywords']) && is_array($json['keywords'])
            ? $json['keywords']
            : [];

        $readingTime = $this->calculateReadingTime($content);
        $qualityScore = $this->assessQuality($title, $content, $keywords);

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
                $coverUrl = $this->generateCoverImage($title, $excerpt, $categoryId);
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

    private function generateCoverImage(string $title, string $excerpt, ?string $categoryId): ?string
    {
        $provider = (string) config('services.image_generation.provider', 'bfl');

        return match ($provider) {
            'bfl' => $this->generateCoverImageWithBfl($title, $excerpt, $categoryId)
                ?? $this->generateCoverImageWithOpenAi($title, $excerpt, $categoryId),
            'openai' => $this->generateCoverImageWithOpenAi($title, $excerpt, $categoryId),
            default => $this->generateCoverImageWithBfl($title, $excerpt, $categoryId)
                ?? $this->generateCoverImageWithOpenAi($title, $excerpt, $categoryId),
        };
    }

    private function generateCoverImageWithOpenAi(string $title, string $excerpt, ?string $categoryId): ?string
    {
        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            return null;
        }

        $prompt = $this->buildCoverPrompt($title, $excerpt, $categoryId);

        $response = Http::withToken($apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/images/generations', [
                'model'   => 'dall-e-3',
                'prompt'  => Str::limit($prompt, 4000),
                'n'       => 1,
                'size'    => '1792x1024',
                'quality' => 'standard',
            ]);

        if ($response->failed()) {
            $message = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException("DALL-E API error: {$message}");
        }

        $imageUrl = $response->json('data.0.url');

        if (! is_string($imageUrl) || $imageUrl === '') {
            return null;
        }

        return $this->persistGeneratedImage($imageUrl, $title);
    }

    private function generateCoverImageWithBfl(string $title, string $excerpt, ?string $categoryId): ?string
    {
        $apiKey = (string) config('services.bfl.api_key');
        if ($apiKey === '') {
            return null;
        }

        $baseUrl = rtrim((string) config('services.bfl.base_url', 'https://api.bfl.ai/v1'), '/');
        $model = (string) config('services.bfl.model', 'flux-pro-1.1-ultra');
        $prompt = $this->buildCoverPrompt($title, $excerpt, $categoryId);

        $payload = [
            'prompt' => Str::limit($prompt, 2000),
            'aspect_ratio' => (string) config('services.bfl.aspect_ratio', '16:9'),
            'output_format' => 'jpeg',
            'prompt_upsampling' => (bool) config('services.bfl.prompt_upsampling', false),
            'safety_tolerance' => (int) config('services.bfl.safety_tolerance', 2),
        ];

        if ((bool) config('services.bfl.raw', true) && str_contains($model, 'ultra')) {
            $payload['raw'] = true;
        }

        $response = Http::withHeaders([
            'x-key' => $apiKey,
            'accept' => 'application/json',
        ])->timeout(60)->post($baseUrl.'/'.$model, $payload);

        if ($response->failed()) {
            $message = $response->json('detail')
                ?? $response->json('error')
                ?? $response->body();
            throw new \RuntimeException("BFL API error: {$message}");
        }

        $pollingUrl = (string) ($response->json('polling_url') ?? '');
        if ($pollingUrl === '') {
            throw new \RuntimeException('BFL did not return a polling_url.');
        }

        $maxAttempts = max(1, (int) config('services.bfl.poll_max_attempts', 25));
        $sleepMs = max(250, (int) config('services.bfl.poll_sleep_ms', 2000));

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            usleep($sleepMs * 1000);

            $pollResponse = Http::withHeaders([
                'x-key' => $apiKey,
                'accept' => 'application/json',
            ])->timeout(60)->get($pollingUrl);

            if ($pollResponse->failed()) {
                continue;
            }

            $status = mb_strtolower((string) ($pollResponse->json('status') ?? ''));
            if (in_array($status, ['ready', 'completed', 'complete', 'succeeded'], true)) {
                $imageUrl = (string) ($pollResponse->json('result.sample') ?? $pollResponse->json('sample') ?? $pollResponse->json('result.url') ?? $pollResponse->json('url') ?? '');

                if ($imageUrl === '') {
                    throw new \RuntimeException('BFL result did not contain an image URL.');
                }

                return $this->persistGeneratedImage($imageUrl, $title);
            }

            if (in_array($status, ['error', 'failed'], true)) {
                $message = $pollResponse->json('error')
                    ?? $pollResponse->json('message')
                    ?? 'Image generation failed.';
                throw new \RuntimeException((string) $message);
            }
        }

        throw new \RuntimeException('BFL image generation timed out.');
    }

    private function isCloudinaryConfigured(): bool
    {
        return (string) config('services.cloudinary.cloud_name') !== ''
            && (
                ((string) config('services.cloudinary.upload_preset') !== '')
                || (
                    (string) config('services.cloudinary.api_key') !== ''
                    && (string) config('services.cloudinary.api_secret') !== ''
                )
            );
    }

    private function uploadRemoteImageToCloudinary(string $imageUrl, string $title): string
    {
        $cloudName = (string) config('services.cloudinary.cloud_name');
        $folder = (string) config('services.cloudinary.submission_folder', 'vivat/generated-covers');
        $publicId = Str::slug(Str::limit($title, 60)) . '-' . Str::lower(Str::random(8));

        $payload = [
            'file'      => $imageUrl,
            'folder'    => $folder,
            'public_id' => $publicId,
        ];

        $uploadPreset = (string) config('services.cloudinary.upload_preset');

        if ($uploadPreset !== '') {
            $payload['upload_preset'] = $uploadPreset;
        } else {
            $timestamp = time();
            $payload['timestamp'] = $timestamp;
            $payload['api_key'] = (string) config('services.cloudinary.api_key');

            $paramsToSign = ['folder' => $folder, 'public_id' => $publicId, 'timestamp' => $timestamp];
            ksort($paramsToSign);
            $stringToSign = collect($paramsToSign)
                ->filter(fn ($v) => $v !== null && $v !== '')
                ->map(fn ($v, $k) => $k . '=' . $v)
                ->implode('&');
            $payload['signature'] = sha1($stringToSign . (string) config('services.cloudinary.api_secret'));
        }

        $response = Http::post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Cloudinary upload failed: ' . ($response->json('error.message') ?? $response->body()));
        }

        $secureUrl = (string) $response->json('secure_url');
        if ($secureUrl === '') {
            throw new \RuntimeException('Cloudinary did not return a secure_url.');
        }

        return $secureUrl;
    }

    private function persistGeneratedImage(string $imageUrl, string $title): string
    {
        if ($this->isCloudinaryConfigured()) {
            return $this->uploadRemoteImageToCloudinary($imageUrl, $title);
        }

        return $this->downloadGeneratedImageLocally($imageUrl, $title);
    }

    private function downloadGeneratedImageLocally(string $imageUrl, string $title): string
    {
        $response = Http::timeout(60)->get($imageUrl);

        if ($response->failed()) {
            throw new \RuntimeException('Unable to download generated image.');
        }

        $directory = public_path('uploads/generated-covers');
        File::ensureDirectoryExists($directory);

        $filename = Str::slug(Str::limit($title, 60)) . '-' . Str::lower(Str::random(8)) . '.jpg';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($path, $response->body());

        return '/uploads/generated-covers/' . $filename;
    }

    private function buildCoverPrompt(string $title, string $excerpt, ?string $categoryId): string
    {
        $categoryName = $categoryId ? (\App\Models\Category::find($categoryId)?->name ?? '') : '';
        $summary = trim(Str::limit(strip_tags($excerpt), 240, ''));

        $sceneHint = $this->coverSceneHint($title, $summary, $categoryName);
        $negativeHint = $this->coverNegativeHint($title, $summary, $categoryName);

        $prompt = 'Realistic editorial magazine photography for a Belgian media article. ';
        $prompt .= 'Natural light, authentic everyday setting, credible composition, horizontal cover image, subtle photojournalistic style. ';
        $prompt .= 'The image must directly represent the article topic with a concrete real-world scene, not a generic travel or stock image. ';
        $prompt .= 'No text, no letters, no logo, no watermark, no illustration, no 3D, no fake glossy advertising look, no obvious AI look, no plastic skin, no distorted hands. ';
        $prompt .= 'The image must look like a genuine candid photo selected by an editor. ';
        $prompt .= 'Article title: "' . $title . '". ';

        if ($categoryName !== '') {
            $prompt .= 'Category: ' . $categoryName . '. ';
        }

        if ($summary !== '') {
            $prompt .= 'Article summary: ' . $summary . '. ';
        }

        if ($sceneHint !== '') {
            $prompt .= 'Preferred scene: ' . $sceneHint . '. ';
        }

        if ($negativeHint !== '') {
            $prompt .= 'Avoid: ' . $negativeHint . '. ';
        }

        return Str::limit($prompt, 1200, '');
    }

    private function coverSceneHint(string $title, string $summary, string $categoryName): string
    {
        $text = mb_strtolower($title . ' ' . $summary . ' ' . $categoryName);

        return match (true) {
            str_contains($text, 'sobriété énergétique'),
            str_contains($text, 'empreinte'),
            str_contains($text, 'énergie'),
            str_contains($text, 'consommation') => 'a realistic home interior in Belgium with simple energy-saving gestures, such as adjusting a thermostat, switching off lights, insulating windows, or reducing electricity use',

            str_contains($text, 'finance'),
            str_contains($text, 'budget'),
            str_contains($text, 'épargne') => 'a realistic everyday financial scene, such as a person reviewing household expenses, using a calculator, or managing bills at a kitchen table',

            str_contains($text, 'santé'),
            str_contains($text, 'bien-être') => 'a natural health-related daily life scene, calm and credible, without hospital drama or exaggerated medical imagery',

            str_contains($text, 'voyage') => 'a realistic local travel moment in Europe or Belgium, natural and understated, not luxury tourism',

            str_contains($text, 'technologie') => 'a realistic modern tech usage scene in daily life, subtle and credible, without futuristic sci-fi aesthetics',

            str_contains($text, 'famille') => 'a natural family daily-life scene, authentic and warm, without posed studio look',

            str_contains($text, 'maison'),
            str_contains($text, 'chez soi'),
            str_contains($text, 'habitat') => 'a realistic home and living scene, useful and grounded in everyday life',

            default => 'a realistic editorial photo linked directly to the article subject, grounded in everyday life in Belgium',
        };
    }

    private function coverNegativeHint(string $title, string $summary, string $categoryName): string
    {
        $text = mb_strtolower($title . ' ' . $summary . ' ' . $categoryName);

        $avoid = ['tropical beach', 'luxury resort', 'vacation postcard', 'fantasy scenery', 'generic sunset stock photo'];

        if (str_contains($text, 'énergie') || str_contains($text, 'sobriété énergétique') || str_contains($text, 'empreinte')) {
            $avoid[] = 'travel imagery';
            $avoid[] = 'holiday atmosphere';
        }

        if (str_contains($text, 'finance') || str_contains($text, 'budget')) {
            $avoid[] = 'gold bars';
            $avoid[] = 'cartoon money symbolism';
        }

        return implode(', ', $avoid);
    }

    private function sanitizeContent(string $text): string
    {
        $text = str_replace(["\xe2\x80\x94", "\xe2\x80\x93"], ['&mdash;', '&mdash;'], $text);
        $text = preg_replace('/[\x{201C}\x{201D}]/u', '"', $text);
        $text = preg_replace('/[\x{2018}\x{2019}]/u', "'", $text);
        return trim($text);
    }

    private function sanitizeMetaText(?string $text, int $limit = 190): string
    {
        $cleaned = $this->sanitizeContent((string) $text);
        $cleaned = trim(strip_tags(html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $cleaned = preg_replace('/\s+/u', ' ', $cleaned ?? '');

        return Str::limit(trim((string) $cleaned), $limit, '...');
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
