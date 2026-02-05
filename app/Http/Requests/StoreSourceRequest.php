<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSourceRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'base_url' => 'required|string|url|max:500',
            'language' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('language')) {
            $this->merge(['language' => 'fr']);
        }
        if (! $this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
