<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    private function resolveCoverImageUrl(): string
    {
        $cover = $this->cover_image_url;
        if (empty($cover)
            || (is_string($cover) && stripos($cover, 'picsum') !== false)
            || (is_string($cover) && ! str_starts_with($cover, 'http') && ! str_starts_with($cover, '/uploads/'))) {
            return vivat_category_fallback_image(
                $this->relationLoaded('category') ? $this->category?->slug : null,
                800,
                600,
                (string) $this->id,
                'api'
            );
        }

        return $cover;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isDetailRoute = $request->routeIs(
            'articles.show',
            'articles.generate',
            'articles.generate-async',
            'articles.publish',
            'public.articles.show'
        );

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            ...($isDetailRoute ? ['content' => $this->content] : []),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'keywords' => $this->keywords,
            'category_id' => $this->category_id,
            'language' => $this->language ?? 'fr',
            'sub_category_id' => $this->sub_category_id,
            'cluster_id' => $this->cluster_id,
            'reading_time' => $this->reading_time,
            'status' => $this->status,
            'article_type' => $this->article_type, // hot_news | long_form | standard pour affichage home
            'cover_image_url' => $this->resolveCoverImageUrl(),
            'cover_video_url' => $this->cover_video_url,
            'quality_score' => $this->quality_score,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'sub_category' => $this->whenLoaded('subCategory', fn () => new SubCategoryResource($this->subCategory)),
            ...($isDetailRoute ? ['article_sources' => $this->whenLoaded('articleSources', fn () => ArticleSourceResource::collection($this->articleSources))] : []),
        ];
    }
}
