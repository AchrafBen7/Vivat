<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CoverImageService
{
    public function __construct(private readonly ArticlePromptBuilder $promptBuilder) {}

    public function generate(string $title, string $excerpt, ?string $categoryId, array $options = []): ?string
    {
        $provider = (string) config('services.image_generation.provider', 'bfl');
        $maxAttempts = max(1, (int) ($options['max_attempts'] ?? config('services.image_generation.max_attempts', 3)));
        $lastError = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $imageUrl = match ($provider) {
                    'pexels' => $this->searchPexels($title, $excerpt, $categoryId, $options),
                    'bfl'    => $this->generateWithBfl($title, $excerpt, $categoryId, $options)
                        ?? $this->generateWithOpenAi($title, $excerpt, $categoryId, $options),
                    'openai' => $this->generateWithOpenAi($title, $excerpt, $categoryId, $options),
                    default  => $this->generateWithBfl($title, $excerpt, $categoryId, $options)
                        ?? $this->generateWithOpenAi($title, $excerpt, $categoryId, $options),
                };

                if (! is_string($imageUrl) || $imageUrl === '') {
                    return null;
                }

                // Pexels photos are real photos — no AI vision check needed
                if ($provider !== 'pexels' && $this->shouldRejectImage($imageUrl, $title, $excerpt, $options)) {
                    $lastError = 'Image rejetée par le contrôle qualité visuel (texte/logo/watermark détecté ou visuel non conforme).';
                    continue;
                }

                return $this->persist($imageUrl, $title);
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }
        }

        if ($lastError !== null) {
            throw new \RuntimeException($lastError);
        }

        return null;
    }

    /**
     * Recherche une vraie photo sur Pexels basée sur le sujet extrait par GPT-4o mini.
     * Retourne l'URL de la photo originale (landscape, max qualité).
     */
    private function searchPexels(string $title, string $excerpt, ?string $categoryId, array $options = []): ?string
    {
        $apiKey = (string) config('services.pexels.api_key', '');
        if ($apiKey === '') {
            throw new \RuntimeException('Pexels API key not configured (PEXELS_API_KEY).');
        }

        // GPT-4o mini extrait un sujet visuel simple en anglais
        $subject = $this->promptBuilder->extractVisualSubjectPublic($title, $excerpt, $categoryId);

        // Essaie le sujet précis, puis des fallbacks de plus en plus génériques
        $queries = array_filter(array_unique([
            $subject,
            $this->simplifyQuery($subject),
            $this->categoryFallbackQuery($categoryId),
        ]));

        foreach ($queries as $query) {
            $url = $this->fetchBestPexelsPhoto($apiKey, $query);
            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

    private function fetchBestPexelsPhoto(string $apiKey, string $query): ?string
    {
        $response = Http::withHeaders(['Authorization' => $apiKey])
            ->timeout(15)
            ->get('https://api.pexels.com/v1/search', [
                'query'       => $query,
                'per_page'    => 10,
                'orientation' => 'landscape',
                'size'        => 'large',
            ]);

        if ($response->failed()) {
            return null;
        }

        $photos = $response->json('photos') ?? [];
        if (empty($photos)) {
            return null;
        }

        // Choisit aléatoirement parmi les 5 premiers pour varier
        $pick = $photos[array_rand(array_slice($photos, 0, min(5, count($photos))))];

        // large2x = 940px wide, ~500KB — bonne qualité sans surcharger l'upload Cloudinary
        return (string) ($pick['src']['large2x'] ?? $pick['src']['large'] ?? $pick['src']['original'] ?? '');
    }

    /**
     * Simplifie une requête détaillée en 2-3 mots clés pour un fallback plus large.
     */
    private function simplifyQuery(string $subject): string
    {
        // Garde les 3 premiers mots significatifs
        $words  = preg_split('/\s+/', preg_replace('/[^a-zA-Z\s]/', '', $subject) ?? '');
        $stops  = ['a', 'an', 'the', 'of', 'in', 'on', 'at', 'with', 'near', 'and', 'or', 'for', 'close', 'up', 'shot'];
        $kept   = array_filter($words ?? [], fn ($w) => strlen($w) > 2 && ! in_array(strtolower($w), $stops));

        return implode(' ', array_slice(array_values($kept), 0, 3));
    }

    /**
     * Requête de dernier recours basée sur la catégorie de l'article.
     */
    private function categoryFallbackQuery(?string $categoryId): string
    {
        if (! $categoryId) {
            return 'nature environment';
        }

        $name = strtolower(\App\Models\Category::find($categoryId)?->slug ?? '');

        return match (true) {
            str_contains($name, 'energie')   => 'renewable energy',
            str_contains($name, 'famille')   => 'family home',
            str_contains($name, 'finance')   => 'coins savings',
            str_contains($name, 'sante')     => 'healthy food',
            str_contains($name, 'quotidien') => 'everyday life',
            str_contains($name, 'voyage')    => 'travel landscape',
            str_contains($name, 'mode')      => 'fabric textile',
            str_contains($name, 'maison')    => 'house interior',
            str_contains($name, 'techno')    => 'technology device',
            default                          => 'nature environment',
        };
    }

    private function generateWithOpenAi(string $title, string $excerpt, ?string $categoryId, array $options = []): ?string
    {
        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            return null;
        }

        $prompt    = $this->promptBuilder->buildCoverPrompt($title, $excerpt, $categoryId);
        $styleRefs = $this->loadStyleReferenceImages();

        // Avec références de style → image/edits (style transfer)
        if (! empty($styleRefs)) {
            return $this->generateWithGptImage1($apiKey, $prompt, $styleRefs, $options);
        }

        // Sans références → gpt-image-1 génération directe haute qualité
        $response = Http::withToken($apiKey)
            ->timeout((int) ($options['generation_timeout'] ?? 180))
            ->post('https://api.openai.com/v1/images/generations', [
                'model'   => 'gpt-image-1',
                'prompt'  => Str::limit($prompt, 4000),
                'n'       => 1,
                'size'    => '1536x1024',
                'quality' => 'high',
            ]);

        if ($response->failed()) {
            $message = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException("gpt-image-1 API error: {$message}");
        }

        // gpt-image-1 retourne du base64
        $b64 = $response->json('data.0.b64_json');
        if (is_string($b64) && $b64 !== '') {
            $tmpPath = sys_get_temp_dir() . '/vivat-cover-' . Str::lower(Str::random(12)) . '.jpg';
            file_put_contents($tmpPath, base64_decode($b64));
            return 'file://' . $tmpPath;
        }

        // Fallback url (si jamais OpenAI change le format)
        $imageUrl = $response->json('data.0.url');
        return (is_string($imageUrl) && $imageUrl !== '') ? $imageUrl : null;
    }

    /**
     * gpt-image-1 — génère une image en s'inspirant du style d'une image de référence.
     * Choisit une référence aléatoirement parmi celles disponibles dans cover-style-refs/.
     *
     * @param  array<string>  $styleRefPaths  Chemins absolus vers les images de référence
     */
    private function generateWithGptImage1(string $apiKey, string $prompt, array $styleRefPaths, array $options = []): ?string
    {
        // Choisit une référence aléatoire parmi les disponibles
        $refPath = $styleRefPaths[array_rand($styleRefPaths)];

        // Redimensionne l'image à max 1024px pour respecter les limites API
        $resizedPath = $this->resizeImageForApi($refPath);
        if ($resizedPath === null) {
            \Illuminate\Support\Facades\Log::warning('gpt-image-1: impossible de redimensionner la référence', ['path' => $refPath]);
            return null;
        }

        try {
            $stylePrompt = 'Real documentary photograph matching the photographic style of the reference image. '
                . 'Same muted tones, same natural imperfect light, same casual honest feel — but a completely new subject. '
                . 'Eye-level shot, 50mm lens, handheld. NOT aerial, NOT drone, NOT wide landscape, NOT architectural render. '
                . 'Slight grain, real bokeh blur in background, muted desaturated colors, no HDR, no lens flare, no sun rays. '
                . 'Must look like a real press photo taken on assignment — not AI-generated, not CGI, not a commercial shoot. '
                . 'Forbidden: aerial view, panoramic wide shot, perfect glossy surfaces, saturated colors, dramatic skies, cartoon textures, 3D render look. '
                . 'Subject to photograph (ONE thing only, close-up, eye level): '
                . $prompt;

            $response = Http::withToken($apiKey)
                ->timeout((int) ($options['generation_timeout'] ?? 180))
                ->asMultipart()
                ->attach('image', file_get_contents($resizedPath), 'reference.jpg', ['Content-Type' => 'image/jpeg'])
                ->post('https://api.openai.com/v1/images/edits', [
                    'model'   => 'gpt-image-1',
                    'prompt'  => Str::limit($stylePrompt, 4000),
                    'n'       => '1',
                    'size'    => '1536x1024',
                    'quality' => 'high',
                ]);
        } finally {
            // Supprime le fichier temporaire redimensionné
            if ($resizedPath !== $refPath && file_exists($resizedPath)) {
                @unlink($resizedPath);
            }
        }

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::warning('gpt-image-1 failed, will fallback to dall-e-3', [
                'error' => $response->json('error.message') ?? $response->body(),
            ]);
            return null;
        }

        // gpt-image-1 retourne l'image en base64
        $b64 = $response->json('data.0.b64_json');
        if (! is_string($b64) || $b64 === '') {
            return null;
        }

        // Sauvegarde locale temporaire pour persist()
        $tmpPath = sys_get_temp_dir() . '/vivat-cover-' . Str::lower(Str::random(12)) . '.jpg';
        file_put_contents($tmpPath, base64_decode($b64));

        return 'file://' . $tmpPath;
    }

    /**
     * Redimensionne une image à max 1024px de large/haut (limite API OpenAI).
     * Retourne le chemin du fichier redimensionné (temporaire) ou null en cas d'échec.
     */
    private function resizeImageForApi(string $sourcePath): ?string
    {
        if (! function_exists('imagecreatefromjpeg')) {
            // GD non disponible — retourne l'original si < 4MB
            return filesize($sourcePath) < 4 * 1024 * 1024 ? $sourcePath : null;
        }

        [$origW, $origH, $type] = getimagesize($sourcePath);
        $maxDim = 1024;

        // Pas besoin de redimensionner
        if ($origW <= $maxDim && $origH <= $maxDim) {
            return $sourcePath;
        }

        $ratio  = min($maxDim / $origW, $maxDim / $origH);
        $newW   = (int) round($origW * $ratio);
        $newH   = (int) round($origH * $ratio);

        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG  => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($sourcePath),
            default        => false,
        };

        if (! $src) {
            return null;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $tmpPath = sys_get_temp_dir() . '/vivat-ref-' . Str::lower(Str::random(8)) . '.jpg';
        $ok = imagejpeg($dst, $tmpPath, 90);

        imagedestroy($src);
        imagedestroy($dst);

        return $ok ? $tmpPath : null;
    }

    /**
     * Charge les images de référence.
     * Priorité : URLs configurées dans COVER_STYLE_REF_URLS (séparées par des virgules)
     * Fallback  : fichiers locaux dans storage/app/cover-style-refs/
     *
     * @return array<string>  Chemins locaux (téléchargés si nécessaire)
     */
    private function loadStyleReferenceImages(): array
    {
        $urls = config('services.image_generation.style_ref_urls', '');

        if (is_string($urls) && $urls !== '') {
            $paths = [];
            foreach (array_filter(array_map('trim', explode(',', $urls))) as $url) {
                $path = $this->downloadRefToTemp($url);
                if ($path !== null) {
                    $paths[] = $path;
                }
            }
            return $paths;
        }

        // Fallback : dossier local
        $dir   = storage_path('app/cover-style-refs');
        $files = is_dir($dir) ? (glob($dir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: []) : [];
        return array_values(array_filter($files, fn ($f) => is_readable($f) && filesize($f) > 0));
    }

    /**
     * Télécharge une URL distante (Cloudinary ou autre) dans un fichier temporaire.
     */
    private function downloadRefToTemp(string $url): ?string
    {
        try {
            $response = Http::timeout(30)->get($url);
            if ($response->failed()) {
                return null;
            }
            $ext     = str_ends_with(parse_url($url, PHP_URL_PATH) ?? '', '.png') ? 'png' : 'jpg';
            $tmpPath = sys_get_temp_dir() . '/vivat-styleref-' . Str::lower(Str::random(8)) . '.' . $ext;
            file_put_contents($tmpPath, $response->body());
            return $tmpPath;
        } catch (\Throwable) {
            return null;
        }
    }

    private function generateWithBfl(string $title, string $excerpt, ?string $categoryId, array $options = []): ?string
    {
        $apiKey = (string) config('services.bfl.api_key');
        if ($apiKey === '') {
            return null;
        }

        $baseUrl = rtrim((string) config('services.bfl.base_url', 'https://api.bfl.ai/v1'), '/');
        $model = (string) config('services.bfl.model', 'flux-pro-1.1-ultra');
        $prompt = $this->promptBuilder->buildCoverPrompt($title, $excerpt, $categoryId);

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
        ])->timeout((int) ($options['generation_timeout'] ?? 60))->post($baseUrl.'/'.$model, $payload);

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
            ])->timeout((int) ($options['generation_timeout'] ?? 60))->get($pollingUrl);

            if ($pollResponse->failed()) {
                continue;
            }

            $status = mb_strtolower((string) ($pollResponse->json('status') ?? ''));
            if (in_array($status, ['ready', 'completed', 'complete', 'succeeded'], true)) {
                $imageUrl = (string) ($pollResponse->json('result.sample') ?? $pollResponse->json('sample') ?? $pollResponse->json('result.url') ?? $pollResponse->json('url') ?? '');

                if ($imageUrl === '') {
                    throw new \RuntimeException('BFL result did not contain an image URL.');
                }

                return $imageUrl;
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

    private function shouldRejectImage(string $imageUrl, string $title, string $excerpt, array $options = []): bool
    {
        if (! (bool) ($options['vision_check'] ?? config('services.image_generation.vision_check', true))) {
            return false;
        }

        $apiKey = (string) config('services.openai.api_key');
        if ($apiKey === '') {
            return false;
        }

        $topic = trim($title . ' ' . $excerpt);

        $response = Http::withToken($apiKey)
            ->timeout((int) ($options['vision_timeout'] ?? 60))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a strict editorial image quality checker. Reject any image containing visible text, letters, words, captions, watermarks, logos, readable signs, or interface text. Reject images that do not plausibly match the article topic. Return only valid JSON.',
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Check this generated article cover. Topic: ' . $topic . '. Return JSON with keys: has_text (boolean), has_logo (boolean), topic_match (boolean), reject (boolean), reason (string). Reject if any text/logo/watermark/sign is visible or if the image does not match the topic.',
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => $imageUrl,
                                ],
                            ],
                        ],
                    ],
                ],
                'max_tokens' => 220,
                'temperature' => 0,
            ]);

        if ($response->failed()) {
            return false;
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content) || $content === '') {
            return false;
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            return false;
        }

        return (bool) ($decoded['reject'] ?? false)
            || (bool) ($decoded['has_text'] ?? false)
            || (bool) ($decoded['has_logo'] ?? false)
            || ! (bool) ($decoded['topic_match'] ?? true);
    }

    private function persist(string $imageUrl, string $title): string
    {
        // gpt-image-1 retourne un fichier local temporaire (base64 décodé)
        if (str_starts_with($imageUrl, 'file://')) {
            $localPath = substr($imageUrl, 7);
            try {
                if ($this->isCloudinaryConfigured()) {
                    return $this->uploadToCloudinary('data:image/jpeg;base64,' . base64_encode(file_get_contents($localPath)), $title);
                }
                // Déplace le fichier temporaire dans le dossier public
                $directory = public_path('uploads/generated-covers');
                File::ensureDirectoryExists($directory);
                $filename = Str::slug(Str::limit($title, 60)) . '-' . Str::lower(Str::random(8)) . '.jpg';
                rename($localPath, $directory . '/' . $filename);
                return '/uploads/generated-covers/' . $filename;
            } finally {
                if (file_exists($localPath)) {
                    @unlink($localPath);
                }
            }
        }

        if ($this->isCloudinaryConfigured()) {
            return $this->uploadToCloudinary($imageUrl, $title);
        }

        return $this->downloadLocally($imageUrl, $title);
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

    private function uploadToCloudinary(string $imageUrl, string $title): string
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

    private function downloadLocally(string $imageUrl, string $title): string
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
}
