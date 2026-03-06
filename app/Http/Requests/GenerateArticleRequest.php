<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateArticleRequest extends FormRequest
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
            'item_ids' => 'required|array|min:1|max:10',
            'item_ids.*' => 'uuid|exists:rss_items,id',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'custom_prompt' => 'nullable|string|max:1000',
            'article_type' => 'nullable|string|in:hot_news,long_form,standard',
            'suggested_min_words' => 'nullable|integer|min:300|max:3000',
            'suggested_max_words' => 'nullable|integer|min:300|max:3000',
            'context_priority' => 'nullable|string|max:500',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('custom_prompt') && is_string($this->custom_prompt)) {
            $this->merge(['custom_prompt' => strip_tags($this->custom_prompt)]);
        }
    }
}
