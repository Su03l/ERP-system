<?php

namespace App\Http\Requests;

use App\Enums\AddOnStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreAddOnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('add_ons.create');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['prohibited'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('add_ons', 'code')],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'price_monthly' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'price_yearly' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'status' => ['required', Rule::enum(AddOnStatus::class)],
            'feature_key' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
