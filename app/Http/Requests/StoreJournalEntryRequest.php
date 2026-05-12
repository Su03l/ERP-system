<?php

namespace App\Http\Requests;

use App\Enums\JournalEntrySource;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();

        return [
            'company_id' => ['prohibited'],
            'journal_number' => ['required', 'string', 'max:255', Rule::unique('journal_entries', 'journal_number')->where('company_id', $companyId)],
            'entry_date' => ['required', 'date'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'source' => ['nullable', Rule::enum(JournalEntrySource::class)],
            'source_type' => ['nullable', 'string', 'max:255'],
            'source_id' => ['nullable', 'integer'],
            'status' => ['prohibited'],
            'posted_by' => ['prohibited'],
            'posted_at' => ['prohibited'],
            'approved_by' => ['prohibited'],
            'approved_at' => ['prohibited'],
            'workflow_instance_id' => ['nullable', 'integer', Rule::exists('workflow_instances', 'id')->where('company_id', $companyId)],
            'metadata' => ['nullable', 'array'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', Rule::exists('accounts', 'id')->where('company_id', $companyId)],
            'lines.*.description_ar' => ['nullable', 'string'],
            'lines.*.description_en' => ['nullable', 'string'],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
            'lines.*.line_order' => ['nullable', 'integer', 'min:1'],
            'lines.*.metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateLineTotals($validator);
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function validateLineTotals(Validator $validator): void
    {
        if ($validator->errors()->has('lines') || ! is_array($this->input('lines'))) {
            return;
        }

        $debitTotal = 0;
        $creditTotal = 0;

        foreach ($this->input('lines', []) as $index => $line) {
            if (! is_array($line)) {
                continue;
            }

            $debit = $this->toCents($line['debit'] ?? 0);
            $credit = $this->toCents($line['credit'] ?? 0);

            if ($debit > 0 && $credit > 0) {
                $validator->errors()->add("lines.{$index}.debit", __('accounting.validation.journal_entries.single_side'));
            }

            if ($debit === 0 && $credit === 0) {
                $validator->errors()->add("lines.{$index}.debit", __('accounting.validation.journal_entries.non_zero_line'));
            }

            $debitTotal += $debit;
            $creditTotal += $credit;
        }

        if ($debitTotal !== $creditTotal) {
            $validator->errors()->add('lines', __('accounting.validation.journal_entries.unbalanced'));
        }
    }

    private function toCents(mixed $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
