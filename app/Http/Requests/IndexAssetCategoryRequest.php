<?php

namespace App\Http\Requests;

use App\Enums\AssetCategoryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAssetCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(AssetCategoryStatus::class)],
        ];
    }
}
