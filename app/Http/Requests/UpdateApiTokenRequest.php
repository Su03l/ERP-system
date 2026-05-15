<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateApiTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        $token = $this->route('company_api_token');

        return $token !== null && ($this->user()?->can('revoke', $token) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
