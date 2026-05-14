<?php

namespace App\Http\Requests;

use App\Enums\CompanyAddOnStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateCompanyAddOnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('company_add_ons.manage');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['prohibited'],
            'add_on_id' => ['sometimes', 'required', 'integer', Rule::exists('add_ons', 'id')],
            'status' => ['sometimes', 'required', Rule::enum(CompanyAddOnStatus::class)],
            'starts_at' => ['sometimes', 'required', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
