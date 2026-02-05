<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleSourceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->article_id,
            'rss_item_id' => $this->rss_item_id,
            'source_id' => $this->source_id,
            'url' => $this->url,
            'used_at' => $this->used_at?->toIso8601String(),
        ];
    }
}
