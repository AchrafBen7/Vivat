<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:articles,slug',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'language' => 'nullable|in:fr,nl',
            'sub_category_id' => 'nullable|uuid|exists:sub_categories,id',
            'reading_time' => 'nullable|integer|min:1|max:60',
            'status' => 'nullable|in:draft,review,published,archived,rejected',
            'article_type' => 'nullable|string|in:hot_news,long_form,standard',
            'cover_image_url' => 'nullable|string|max:500',
            'cover_video_url' => 'nullable|string|max:500',
            'quality_score' => 'nullable|integer|min:0|max:100',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('status')) {
            $this->merge(['status' => 'draft']);
        }
        if (! $this->has('reading_time')) {
            $this->merge(['reading_time' => 5]);
        }
        if (! $this->has('language')) {
            $this->merge(['language' => 'fr']);
        }
    }
}
