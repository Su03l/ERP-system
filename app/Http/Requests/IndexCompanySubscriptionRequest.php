<?php

namespace App\Http\Requests;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class IndexCompanySubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('subscriptions.view');
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(SubscriptionStatus::class)],
            'plan_id' => ['nullable', 'integer', Rule::exists('plans', 'id')],
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],
            'billing_cycle' => ['nullable', Rule::enum(BillingCycle::class)],
            'starts_from' => ['nullable', 'date'],
            'starts_until' => ['nullable', 'date', 'after_or_equal:starts_from'],
            'ends_from' => ['nullable', 'date'],
            'ends_until' => ['nullable', 'date', 'after_or_equal:ends_from'],
        ];
    }
}
