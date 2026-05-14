<?php

namespace App\Http\Requests;

use App\Enums\CompanyAddOnStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class IndexCompanyAddOnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('company_add_ons.manage');
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(CompanyAddOnStatus::class)],
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],
            'add_on_id' => ['nullable', 'integer', Rule::exists('add_ons', 'id')],
            'starts_from' => ['nullable', 'date'],
            'starts_until' => ['nullable', 'date', 'after_or_equal:starts_from'],
            'ends_from' => ['nullable', 'date'],
            'ends_until' => ['nullable', 'date', 'after_or_equal:ends_from'],
        ];
    }
}
