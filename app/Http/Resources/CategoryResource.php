<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'home_order' => $this->home_order,
            'image_url' => $this->image_url,
            'video_url' => $this->video_url,
            'published_articles_count' => $this->when(isset($this->published_articles_count), $this->published_articles_count),
            'sub_categories' => $this->whenLoaded('subCategories', fn () => SubCategoryResource::collection($this->subCategories)),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
