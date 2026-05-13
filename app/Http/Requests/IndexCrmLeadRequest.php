<?php

namespace App\Http\Requests;

use App\Enums\LeadStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCrmLeadRequest extends FormRequest
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
            'status' => ['nullable', Rule::enum(LeadStatus::class)],
            'assigned_user_id' => ['nullable', 'integer'],
            'assigned_user' => ['nullable', 'integer'],
            'source' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'created_from' => ['nullable', 'date'],
            'created_until' => ['nullable', 'date', 'after_or_equal:created_from'],
        ];
    }
}
