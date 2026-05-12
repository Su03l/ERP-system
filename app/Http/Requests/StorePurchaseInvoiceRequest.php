<?php

namespace App\Http\Requests;

use App\Enums\InvoiceStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $companyId = (int) ($this->user()?->company_id ?? 0);

        return [
            'company_id' => ['prohibited'],
            'vendor_id' => ['nullable', 'integer', Rule::exists('vendors', 'id')->where('company_id', $companyId)],
            'invoice_number' => ['required', 'string', 'max:255', Rule::unique('purchase_invoices', 'invoice_number')->where('company_id', $companyId)],
            'vendor_invoice_number' => ['nullable', 'string', 'max:255', Rule::unique('purchase_invoices', 'vendor_invoice_number')->where('company_id', $companyId)],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'status' => ['sometimes', Rule::enum(InvoiceStatus::class)],
            'subtotal' => ['prohibited'],
            'tax_amount' => ['prohibited'],
            'discount_amount' => ['prohibited'],
            'total_amount' => ['prohibited'],
            'balance_due' => ['prohibited'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'notes_ar' => ['nullable', 'string'],
            'notes_en' => ['nullable', 'string'],
            'posted_journal_entry_id' => ['nullable', 'integer', Rule::exists('journal_entries', 'id')->where('company_id', $companyId)],
            'metadata' => ['nullable', 'array'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description_ar' => ['required', 'string', 'max:255'],
            'lines.*.description_en' => ['nullable', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.tax_amount' => ['prohibited'],
            'lines.*.line_total' => ['prohibited'],
            'lines.*.metadata' => ['nullable', 'array'],
        ];
    }
}
