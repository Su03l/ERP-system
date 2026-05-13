<?php

namespace App\Http\Requests;

use App\Enums\AssetStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAssetRequest extends FormRequest
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
            'asset_category_id' => ['nullable', 'integer'],
            'category' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(AssetStatus::class)],
            'assigned_employee_id' => ['nullable', 'integer'],
            'assigned_employee' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:255'],
            'purchased_from' => ['nullable', 'date'],
            'purchased_until' => ['nullable', 'date', 'after_or_equal:purchased_from'],
        ];
    }
}
