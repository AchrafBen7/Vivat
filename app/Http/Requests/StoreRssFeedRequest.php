<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRssFeedRequest extends FormRequest
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
            'source_id' => 'nullable|uuid|exists:sources,id',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'feed_url' => 'required|string|url|max:2000',
            'is_active' => 'nullable|boolean',
            'fetch_interval_minutes' => 'nullable|integer|min:5|max:1440',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
        if (! $this->has('fetch_interval_minutes')) {
            $this->merge(['fetch_interval_minutes' => 30]);
        }
    }
}
