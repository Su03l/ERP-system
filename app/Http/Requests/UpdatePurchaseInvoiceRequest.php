<?php

namespace App\Http\Requests;

use App\Enums\InvoiceStatus;
use App\Models\PurchaseInvoice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->routePurchaseInvoice();

        return $invoice !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $invoice->company_id;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $companyId = (int) ($this->user()?->company_id ?? 0);
        $invoice = $this->routePurchaseInvoice();

        return [
            'company_id' => ['prohibited'],
            'vendor_id' => ['sometimes', 'nullable', 'integer', Rule::exists('vendors', 'id')->where('company_id', $companyId)],
            'invoice_number' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('purchase_invoices', 'invoice_number')->where('company_id', $companyId)->ignore($invoice?->id)],
            'vendor_invoice_number' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('purchase_invoices', 'vendor_invoice_number')->where('company_id', $companyId)->ignore($invoice?->id)],
            'invoice_date' => ['sometimes', 'required', 'date'],
            'due_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:invoice_date'],
            'status' => ['sometimes', Rule::enum(InvoiceStatus::class)],
            'subtotal' => ['prohibited'],
            'tax_amount' => ['prohibited'],
            'discount_amount' => ['prohibited'],
            'total_amount' => ['prohibited'],
            'balance_due' => ['prohibited'],
            'paid_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3'],
            'notes_ar' => ['sometimes', 'nullable', 'string'],
            'notes_en' => ['sometimes', 'nullable', 'string'],
            'posted_journal_entry_id' => ['sometimes', 'nullable', 'integer', Rule::exists('journal_entries', 'id')->where('company_id', $companyId)],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'lines' => ['sometimes', 'array', 'min:1'],
            'lines.*.description_ar' => ['required_with:lines', 'string', 'max:255'],
            'lines.*.description_en' => ['nullable', 'string', 'max:255'],
            'lines.*.quantity' => ['required_with:lines', 'numeric', 'gt:0'],
            'lines.*.unit_price' => ['required_with:lines', 'numeric', 'min:0'],
            'lines.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.tax_amount' => ['prohibited'],
            'lines.*.line_total' => ['prohibited'],
            'lines.*.metadata' => ['nullable', 'array'],
        ];
    }

    private function routePurchaseInvoice(): ?PurchaseInvoice
    {
        $invoice = $this->route('purchase_invoice') ?? $this->route('purchaseInvoice');

        if ($invoice instanceof PurchaseInvoice) {
            return $invoice;
        }

        return $invoice === null ? null : PurchaseInvoice::query()->find($invoice);
    }
}
