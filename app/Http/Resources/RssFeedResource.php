<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RssFeedResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source_id' => $this->source_id,
            'category_id' => $this->category_id,
            'feed_url' => $this->feed_url,
            'is_active' => $this->is_active,
            'last_fetched_at' => $this->last_fetched_at?->toIso8601String(),
            'fetch_interval_minutes' => $this->fetch_interval_minutes,
            'created_at' => $this->created_at?->toIso8601String(),
            'source' => $this->whenLoaded('source', fn () => new SourceResource($this->source)),
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
        ];
    }
}
