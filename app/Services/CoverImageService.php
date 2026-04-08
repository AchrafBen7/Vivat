<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CoverImageService
{
    public function __construct(private readonly ArticlePromptBuilder $promptBuilder) {}

    public function generate(string $title, string $excerpt, ?string $categoryId): ?string
    {
        $provider = (string) config('services.image_generation.provider', 'bfl');

        return match ($provider) {
            'bfl' => $this->generateWithBfl($title, $excerpt, $categoryId)
                ?? $this->generateWithOpenAi($title, $excerpt, $categoryId),
            'openai' => $this->generateWithOpenAi($title, $excerpt, $categoryId),
            default => $this->generateWithBfl($title, $excerpt, $categoryId)
                ?? $this->generateWithOpenAi($title, $excerpt, $categoryId),
        };
    }

    private function generateWithOpenAi(string $title, string $excerpt, ?string $categoryId): ?string
    {
        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            return null;
        }

        $prompt = $this->promptBuilder->buildCoverPrompt($title, $excerpt, $categoryId);

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

        return $this->persist($imageUrl, $title);
    }

    private function generateWithBfl(string $title, string $excerpt, ?string $categoryId): ?string
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

                return $this->persist($imageUrl, $title);
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

    private function persist(string $imageUrl, string $title): string
    {
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
