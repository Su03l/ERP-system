<?php

namespace App\Http\Requests;

use App\Enums\PlanStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('plans.create');
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
            'code' => ['required', 'string', 'max:255', Rule::unique('plans', 'code')],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'price_monthly' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'price_yearly' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'currency' => ['required', 'string', 'size:3'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(PlanStatus::class)],
            'limits' => ['nullable', 'array'],
            'features' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
