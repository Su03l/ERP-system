<?php

namespace App\Http\Requests;

use App\Enums\SubscriptionInvoiceStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class IndexSubscriptionInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('subscription_invoices.view');
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(SubscriptionInvoiceStatus::class)],
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],
            'subscription_id' => ['nullable', 'integer', Rule::exists('company_subscriptions', 'id')],
            'invoice_date_from' => ['nullable', 'date'],
            'invoice_date_until' => ['nullable', 'date', 'after_or_equal:invoice_date_from'],
            'due_date_from' => ['nullable', 'date'],
            'due_date_until' => ['nullable', 'date', 'after_or_equal:due_date_from'],
        ];
    }
}
