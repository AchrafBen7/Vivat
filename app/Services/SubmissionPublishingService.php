<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubmissionPublishingService
{
    private const META_MAX_LENGTH = 190;

    public function __construct(
        private readonly SubmissionImageStorageService $submissionImageStorage,
    ) {}

    public function approveAndPublish(Submission $submission, array $data, ?User $reviewer = null): Article
    {
        return DB::transaction(function () use ($submission, $data, $reviewer): Article {
            if ($submission->status !== 'pending') {
                throw new \RuntimeException('Only pending submissions can be approved and published.');
            }

            $submission->loadMissing(['user', 'category']);

            $categoryId = $data['category_id'] ?? $submission->category_id;
            $articleType = $data['article_type'] ?? 'standard';
            $notes = $data['notes'] ?? $data['reviewer_notes'] ?? null;
            $reviewerId = $data['reviewed_by'] ?? $reviewer?->id;
            $reviewedAt = $data['reviewed_at'] ?? now();
            $language = $this->resolveLanguage($submission);
            $coverImageUrl = $this->submissionImageStorage->migrateLocalImageUrl($submission->cover_image_url);

            if ($coverImageUrl !== $submission->cover_image_url) {
                $submission->update(['cover_image_url' => $coverImageUrl]);
            }

            $metaTitle = $this->sanitizeMetaText($submission->title);
            $metaDescription = $this->sanitizeMetaText(
                $submission->excerpt ?: Str::limit(strip_tags($submission->content), 180, '...')
            );

            $article = Article::create([
                'title' => $submission->title,
                'slug' => $this->generateUniqueSlug($submission->title),
                'excerpt' => $submission->excerpt,
                'content' => $submission->content,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'category_id' => $categoryId,
                'reading_time' => $submission->reading_time ?: 5,
                'status' => 'published',
                'article_type' => $articleType,
                'cover_image_url' => $coverImageUrl,
                'quality_score' => 100,
                'published_at' => now(),
                'language' => $language,
            ]);

            $submission->approve(
                reviewerId: $reviewerId,
                notes: $notes,
                reviewedAt: $reviewedAt,
            );

            return $article;
        });
    }

    public function reject(Submission $submission, array $data, ?User $reviewer = null): bool
    {
        if ($submission->status !== 'pending') {
            throw new \RuntimeException('Only pending submissions can be rejected.');
        }

        return $submission->reject(
            reviewerId: $data['reviewed_by'] ?? $reviewer?->id,
            notes: $data['notes'] ?? $data['reviewer_notes'] ?? null,
            reviewedAt: $data['reviewed_at'] ?? now(),
        );
    }

    private function resolveLanguage(Submission $submission): string
    {
        $language = strtolower((string) ($submission->user?->language ?? 'fr'));

        return in_array($language, ['fr', 'nl'], true) ? $language : 'fr';
    }

    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug !== '' ? $baseSlug : 'article';
        $counter = 1;

        while (Article::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function sanitizeMetaText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');

        if ($normalized === '') {
            return null;
        }

        return Str::limit($normalized, self::META_MAX_LENGTH, '...');
    }
}
