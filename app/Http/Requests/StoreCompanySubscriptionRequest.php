<?php

namespace App\Http\Requests;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreCompanySubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('subscriptions.create');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')],
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')],
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
            'billing_cycle' => ['required', Rule::enum(BillingCycle::class)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'trial_ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'cancelled_at' => ['nullable', 'date'],
            'grace_ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
