<?php

namespace App\Http\Requests;

use App\Enums\AssetCategoryStatus;
use App\Models\AssetCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAssetCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assetCategory = $this->routeAssetCategory();

        return $assetCategory !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $assetCategory->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();
        $assetCategory = $this->routeAssetCategory();

        return [
            'company_id' => ['prohibited'],
            'parent_id' => ['sometimes', 'nullable', 'integer', Rule::exists('asset_categories', 'id')->where('company_id', $companyId)],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'code' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('asset_categories', 'code')->where('company_id', $companyId)->ignore($assetCategory?->id)],
            'status' => ['sometimes', 'required', Rule::enum(AssetCategoryStatus::class)],
            'description_ar' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $assetCategory = $this->routeAssetCategory();
                $parentId = $this->integer('parent_id') ?: null;

                if ($assetCategory === null || $parentId === null) {
                    return;
                }

                if ($parentId === $assetCategory->id) {
                    $validator->errors()->add('parent_id', __('assets.validation.asset_categories.parent_self'));
                }
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routeAssetCategory(): ?AssetCategory
    {
        $assetCategory = $this->route('asset_category') ?? $this->route('assetCategory');

        if ($assetCategory instanceof AssetCategory) {
            return $assetCategory;
        }

        return $assetCategory === null ? null : AssetCategory::query()->find($assetCategory);
    }
}
