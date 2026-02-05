<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RssItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rss_feed_id' => $this->rss_feed_id,
            'category_id' => $this->category_id,
            'guid' => $this->guid,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'published_at' => $this->published_at?->toIso8601String(),
            'fetched_at' => $this->fetched_at?->toIso8601String(),
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'rss_feed' => $this->whenLoaded('rssFeed', fn () => new RssFeedResource($this->rssFeed)),
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'enriched_item' => $this->whenLoaded('enrichedItem', fn () => new EnrichedItemResource($this->enrichedItem)),
        ];
    }
}
