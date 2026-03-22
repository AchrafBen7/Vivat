<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'slug'            => $this->slug,
            'excerpt'         => $this->excerpt,
            'content'         => $this->when(
                $request->routeIs('contributor.submissions.show', 'admin.submissions.show'),
                $this->content
            ),
            'category_id'     => $this->category_id,
            'reading_time'    => $this->reading_time,
            'cover_image_path' => $this->cover_image_path,
            'status'          => $this->status,
            'reviewer_notes'  => $this->reviewer_notes,
            'reviewed_at'     => $this->reviewed_at?->toIso8601String(),
            'payment_id'      => $this->payment_id,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
            'user'            => $this->whenLoaded('user', fn () => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ]),
            'category'        => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'reviewer'        => $this->whenLoaded('reviewer', fn () => [
                'id'   => $this->reviewer->id,
                'name' => $this->reviewer->name,
            ]),
        ];
    }
}
