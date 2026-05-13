<?php

namespace App\Http\Requests;

use App\Enums\AssetCategoryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetCategoryRequest extends FormRequest
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
            'parent_id' => ['nullable', 'integer', Rule::exists('asset_categories', 'id')->where('company_id', $companyId)],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('asset_categories', 'code')->where('company_id', $companyId)],
            'status' => ['required', Rule::enum(AssetCategoryStatus::class)],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }
}
