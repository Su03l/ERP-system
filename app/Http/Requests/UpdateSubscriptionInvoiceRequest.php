<?php

namespace App\Http\Requests;

use App\Enums\SubscriptionInvoiceStatus;
use App\Models\SubscriptionInvoice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateSubscriptionInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('subscription_invoices.mark_paid') || Gate::allows('subscription_invoices.generate');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $invoice = $this->subscriptionInvoice();
        $companyId = (int) ($invoice?->company_id ?? 0);

        return [
            'company_id' => ['prohibited'],
            'subscription_id' => ['sometimes', 'required', 'integer', Rule::exists('company_subscriptions', 'id')->where('company_id', $companyId)],
            'invoice_number' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('subscription_invoices', 'invoice_number')->where('company_id', $companyId)->ignore($invoice?->id)],
            'invoice_date' => ['sometimes', 'required', 'date'],
            'due_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:invoice_date'],
            'status' => ['sometimes', 'required', Rule::enum(SubscriptionInvoiceStatus::class)],
            'subtotal' => ['sometimes', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'tax_amount' => ['sometimes', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'discount_amount' => ['sometimes', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'total_amount' => ['sometimes', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'paid_amount' => ['sometimes', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'balance_due' => ['sometimes', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function subscriptionInvoice(): ?SubscriptionInvoice
    {
        $invoice = $this->route('subscriptionInvoice') ?? $this->route('subscription_invoice');

        if ($invoice instanceof SubscriptionInvoice) {
            return $invoice;
        }

        return is_numeric($invoice) ? SubscriptionInvoice::query()->find((int) $invoice) : null;
    }
}
