<?php

namespace App\Http\Requests;

use App\Enums\ContactStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCrmContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ContactStatus::class)],
            'customer_id' => ['nullable', 'integer'],
            'lead_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:255'],
            'created_from' => ['nullable', 'date'],
            'created_until' => ['nullable', 'date', 'after_or_equal:created_from'],
        ];
    }
}
