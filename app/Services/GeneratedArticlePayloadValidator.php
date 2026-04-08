<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GeneratedArticlePayloadValidator
{
    public function __construct(
        private readonly ArticleContentProcessor $contentProcessor,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *   title: string,
     *   content: string,
     *   excerpt: string,
     *   meta_title: string,
     *   meta_description: string,
     *   keywords: array<int, string>
     * }
     */
    public function validateAndNormalize(array $payload): array
    {
        $normalized = [
            'title' => $this->contentProcessor->sanitizeContent((string) ($payload['title'] ?? '')),
            'content' => $this->contentProcessor->sanitizeHtmlContent((string) ($payload['content'] ?? $payload['body'] ?? '')),
            'excerpt' => $this->contentProcessor->sanitizeContent((string) ($payload['excerpt'] ?? '')),
            'meta_title' => $this->contentProcessor->sanitizeMetaText((string) ($payload['meta_title'] ?? '')),
            'meta_description' => $this->contentProcessor->sanitizeMetaText((string) ($payload['meta_description'] ?? '')),
            'keywords' => $this->normalizeKeywords($payload['keywords'] ?? []),
        ];

        if ($normalized['excerpt'] === '') {
            $normalized['excerpt'] = Str::limit(strip_tags($normalized['content']), 200);
        }

        if ($normalized['meta_title'] === '') {
            $normalized['meta_title'] = $this->contentProcessor->sanitizeMetaText($normalized['title'], 190);
        }

        if ($normalized['meta_description'] === '') {
            $normalized['meta_description'] = $this->contentProcessor->sanitizeMetaText($normalized['excerpt'], 190);
        }

        $validator = Validator::make(
            [
                'title' => $normalized['title'],
                'content' => $normalized['content'],
                'content_text_length' => mb_strlen(trim(strip_tags($normalized['content']))),
                'keywords' => $normalized['keywords'],
            ],
            [
                'title' => ['required', 'string', 'min:8', 'max:190'],
                'content' => ['required', 'string'],
                'content_text_length' => ['integer', 'min:200'],
                'keywords' => ['nullable', 'array'],
                'keywords.*' => ['string', 'min:2', 'max:80'],
            ],
            [
                'content_text_length.min' => 'Le contenu généré est trop court après sanitisation.',
            ]
        );

        if ($validator->fails()) {
            throw new \RuntimeException('Payload article IA invalide : '.$validator->errors()->first());
        }

        return $normalized;
    }

    /**
     * @param  mixed  $keywords
     * @return array<int, string>
     */
    private function normalizeKeywords(mixed $keywords): array
    {
        if (! is_array($keywords)) {
            return [];
        }

        return collect($keywords)
            ->filter(fn ($keyword) => is_string($keyword) || is_numeric($keyword))
            ->map(fn ($keyword) => $this->contentProcessor->sanitizeContent((string) $keyword))
            ->filter(fn (string $keyword) => $keyword !== '')
            ->unique()
            ->values()
            ->all();
    }
}
