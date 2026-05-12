<?php

namespace App\Http\Requests;

use App\Enums\SalaryComponentStatus;
use App\Enums\SalaryComponentType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexSalaryComponentRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::enum(SalaryComponentType::class)],
            'status' => ['nullable', Rule::enum(SalaryComponentStatus::class)],
        ];
    }
}
