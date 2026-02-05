<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->when($request->routeIs('articles.show'), $this->content),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'keywords' => $this->keywords,
            'category_id' => $this->category_id,
            'cluster_id' => $this->cluster_id,
            'reading_time' => $this->reading_time,
            'status' => $this->status,
            'quality_score' => $this->quality_score,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'article_sources' => $this->whenLoaded('articleSources', fn () => ArticleSourceResource::collection($this->articleSources)),
        ];
    }
}
