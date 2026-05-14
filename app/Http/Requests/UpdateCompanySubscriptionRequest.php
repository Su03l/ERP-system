<?php

namespace App\Http\Requests;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateCompanySubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('subscriptions.update');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['prohibited'],
            'plan_id' => ['sometimes', 'required', 'integer', Rule::exists('plans', 'id')],
            'status' => ['sometimes', 'required', Rule::enum(SubscriptionStatus::class)],
            'billing_cycle' => ['sometimes', 'required', Rule::enum(BillingCycle::class)],
            'starts_at' => ['sometimes', 'required', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'trial_ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'cancelled_at' => ['sometimes', 'nullable', 'date'],
            'grace_ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
