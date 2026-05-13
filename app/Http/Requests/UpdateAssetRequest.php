<?php

namespace App\Http\Requests;

use App\Enums\AssetDepreciationMethod;
use App\Enums\AssetStatus;
use App\Models\Asset;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $asset = $this->routeAsset();

        return $asset !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $asset->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();
        $asset = $this->routeAsset();

        return [
            'company_id' => ['prohibited'],
            'asset_category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('asset_categories', 'id')->where('company_id', $companyId)],
            'asset_code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('assets', 'asset_code')->where('company_id', $companyId)->ignore($asset?->id)],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'serial_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'purchase_date' => ['sometimes', 'nullable', 'date'],
            'purchase_cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'current_value' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', Rule::enum(AssetStatus::class)],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'assigned_employee_id' => ['sometimes', 'nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'depreciation_method' => ['sometimes', 'nullable', Rule::enum(AssetDepreciationMethod::class)],
            'useful_life_months' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'salvage_value' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routeAsset(): ?Asset
    {
        $asset = $this->route('asset');

        if ($asset instanceof Asset) {
            return $asset;
        }

        return $asset === null ? null : Asset::query()->find($asset);
    }
}
