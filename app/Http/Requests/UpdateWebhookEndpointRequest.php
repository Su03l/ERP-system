<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->company_id !== null && $user->hasPermission('webhooks.update', $user->company_id);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'url' => ['sometimes', 'url', 'starts_with:https://', 'max:2048'],
            'secret' => ['sometimes', 'nullable', 'string', 'min:16', 'max:255'],
            'events' => ['sometimes', 'array', 'min:1'],
            'events.*' => ['string', 'max:150'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
