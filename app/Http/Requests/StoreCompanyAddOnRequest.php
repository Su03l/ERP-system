<?php

namespace App\Http\Requests;

use App\Enums\CompanyAddOnStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreCompanyAddOnRequest extends FormRequest
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
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')],
            'add_on_id' => ['required', 'integer', Rule::exists('add_ons', 'id')],
            'status' => ['required', Rule::enum(CompanyAddOnStatus::class)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
