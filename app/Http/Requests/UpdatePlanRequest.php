<?php

namespace App\Http\Requests;

use App\Enums\PlanStatus;
use App\Models\Plan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('plans.update');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $plan = $this->plan();

        return [
            'company_id' => ['prohibited'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('plans', 'code')->ignore($plan?->id)],
            'description_ar' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'price_monthly' => ['sometimes', 'required', 'numeric', 'min:0', 'decimal:0,2'],
            'price_yearly' => ['sometimes', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'currency' => ['sometimes', 'required', 'string', 'size:3'],
            'trial_days' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', Rule::enum(PlanStatus::class)],
            'limits' => ['sometimes', 'nullable', 'array'],
            'features' => ['sometimes', 'nullable', 'array'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function plan(): ?Plan
    {
        $plan = $this->route('plan');

        if ($plan instanceof Plan) {
            return $plan;
        }

        return is_numeric($plan) ? Plan::query()->find((int) $plan) : null;
    }
}
