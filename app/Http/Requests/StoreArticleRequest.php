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
            'reading_time' => 'nullable|integer|min:1|max:60',
            'status' => 'nullable|in:draft,review,published,archived,rejected',
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
    }
}
