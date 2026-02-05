<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSourceRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'base_url' => 'sometimes|string|url|max:500',
            'language' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ];
    }
}
