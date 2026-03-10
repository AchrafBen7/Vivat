<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'sometimes|string',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'language' => 'nullable|in:fr,nl',
            'sub_category_id' => 'nullable|uuid|exists:sub_categories,id',
            'status' => 'nullable|in:draft,review,published,archived,rejected',
            'article_type' => 'nullable|string|in:hot_news,long_form,standard',
            'cover_image_url' => 'nullable|string|max:500',
            'cover_video_url' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
        ];
    }
}
