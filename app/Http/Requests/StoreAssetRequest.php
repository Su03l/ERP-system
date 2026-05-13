<?php

namespace App\Http\Requests;

use App\Enums\AssetDepreciationMethod;
use App\Enums\AssetStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
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
        $companyId = $this->companyId();

        return [
            'company_id' => ['prohibited'],
            'asset_category_id' => ['nullable', 'integer', Rule::exists('asset_categories', 'id')->where('company_id', $companyId)],
            'asset_code' => ['required', 'string', 'max:255', Rule::unique('assets', 'asset_code')->where('company_id', $companyId)],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'current_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(AssetStatus::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'assigned_employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'depreciation_method' => ['nullable', Rule::enum(AssetDepreciationMethod::class)],
            'useful_life_months' => ['nullable', 'integer', 'min:1'],
            'salvage_value' => ['nullable', 'numeric', 'min:0'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }
}
