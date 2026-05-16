<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWebhookEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->company_id !== null && $user->hasPermission('webhooks.create', $user->company_id);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'starts_with:https://', 'max:2048'],
            'secret' => ['sometimes', 'nullable', 'string', 'min:16', 'max:255'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'max:150'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
