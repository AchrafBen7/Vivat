<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SubmissionImageStorageService
{
    public function storeUploadedImage(UploadedFile $file): string
    {
        if ($this->isCloudinaryConfigured()) {
            return $this->uploadFileToCloudinary(
                filePath: $file->getRealPath() ?: $file->path(),
                originalName: $file->getClientOriginalName()
            );
        }

        return $this->storeLocally($file);
    }

    public function migrateLocalImageUrl(?string $imageUrl): ?string
    {
        $trimmed = is_string($imageUrl) ? trim($imageUrl) : '';

        if ($trimmed === '' || ! $this->isLocalPublicUploadPath($trimmed) || ! $this->isCloudinaryConfigured()) {
            return $trimmed !== '' ? $trimmed : null;
        }

        $absolutePath = public_path(ltrim($trimmed, '/'));

        if (! File::exists($absolutePath)) {
            return $trimmed;
        }

        $uploadedUrl = $this->uploadFileToCloudinary(
            filePath: $absolutePath,
            originalName: basename($absolutePath)
        );

        File::delete($absolutePath);

        return $uploadedUrl;
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

    private function isLocalPublicUploadPath(string $imageUrl): bool
    {
        return str_starts_with($imageUrl, '/uploads/submissions/');
    }

    private function uploadFileToCloudinary(string $filePath, string $originalName): string
    {
        $folder = (string) config('services.cloudinary.submission_folder', 'vivat/submissions');
        $cloudName = (string) config('services.cloudinary.cloud_name');

        $payload = [
            'folder' => $folder,
            'public_id' => pathinfo($originalName, PATHINFO_FILENAME) . '-' . Str::lower(Str::random(10)),
        ];

        $uploadPreset = (string) config('services.cloudinary.upload_preset');

        if ($uploadPreset !== '') {
            $payload['upload_preset'] = $uploadPreset;
        } else {
            $timestamp = time();
            $payload['timestamp'] = $timestamp;
            $payload['api_key'] = (string) config('services.cloudinary.api_key');
            $payload['signature'] = $this->signPayload([
                'folder' => $payload['folder'],
                'public_id' => $payload['public_id'],
                'timestamp' => $timestamp,
            ]);
        }

        $response = Http::asMultipart()
            ->attach('file', fopen($filePath, 'r'), basename($filePath))
            ->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", $payload);

        $this->throwIfUploadFailed($response);

        $secureUrl = (string) $response->json('secure_url');

        if ($secureUrl === '') {
            throw new \RuntimeException('Cloudinary did not return a secure_url.');
        }

        return $secureUrl;
    }

    private function signPayload(array $params): string
    {
        ksort($params);

        $normalized = collect($params)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value, $key) => $key . '=' . $value)
            ->implode('&');

        return sha1($normalized . (string) config('services.cloudinary.api_secret'));
    }

    private function throwIfUploadFailed(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        throw new \RuntimeException(
            (string) ($response->json('error.message') ?: 'Cloudinary upload failed.')
        );
    }

    private function storeLocally(UploadedFile $file): string
    {
        $directory = public_path('uploads/submissions');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return '/uploads/submissions/' . $filename;
    }
}
