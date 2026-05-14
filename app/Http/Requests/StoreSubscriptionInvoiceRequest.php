<?php

namespace App\Http\Requests;

use App\Enums\SubscriptionInvoiceStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreSubscriptionInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('subscription_invoices.generate');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();

        return [
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')],
            'subscription_id' => ['required', 'integer', Rule::exists('company_subscriptions', 'id')->where('company_id', $companyId)],
            'invoice_number' => ['required', 'string', 'max:255', Rule::unique('subscription_invoices', 'invoice_number')->where('company_id', $companyId)],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'status' => ['required', Rule::enum(SubscriptionInvoiceStatus::class)],
            'subtotal' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'tax_amount' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'total_amount' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'balance_due' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'currency' => ['nullable', 'string', 'size:3'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    private function companyId(): int
    {
        return (int) $this->input('company_id', 0);
    }
}
