<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrichedItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rss_item_id' => $this->rss_item_id,
            'lead' => $this->lead,
            'headings' => $this->headings,
            'key_points' => $this->key_points,
            'quality_score' => $this->quality_score,
            'extraction_method' => $this->extraction_method,
            'enriched_at' => $this->enriched_at?->toIso8601String(),
        ];
    }
}
