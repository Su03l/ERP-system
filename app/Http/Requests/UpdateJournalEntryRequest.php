<?php

namespace App\Http\Requests;

use App\Enums\JournalEntrySource;
use App\Enums\JournalEntryStatus;
use App\Models\JournalEntry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $journalEntry = $this->routeJournalEntry();

        return $journalEntry !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $journalEntry->company_id
            && $journalEntry->status === JournalEntryStatus::Draft;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();
        $journalEntry = $this->routeJournalEntry();

        return [
            'company_id' => ['prohibited'],
            'journal_number' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('journal_entries', 'journal_number')->where('company_id', $companyId)->ignore($journalEntry?->id)],
            'entry_date' => ['sometimes', 'required', 'date'],
            'description_ar' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'source' => ['sometimes', 'nullable', Rule::enum(JournalEntrySource::class)],
            'source_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'source_id' => ['sometimes', 'nullable', 'integer'],
            'status' => ['prohibited'],
            'posted_by' => ['prohibited'],
            'posted_at' => ['prohibited'],
            'approved_by' => ['prohibited'],
            'approved_at' => ['prohibited'],
            'workflow_instance_id' => ['sometimes', 'nullable', 'integer', Rule::exists('workflow_instances', 'id')->where('company_id', $companyId)],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'lines' => ['sometimes', 'array', 'min:2'],
            'lines.*.account_id' => ['required_with:lines', 'integer', Rule::exists('accounts', 'id')->where('company_id', $companyId)],
            'lines.*.description_ar' => ['nullable', 'string'],
            'lines.*.description_en' => ['nullable', 'string'],
            'lines.*.debit' => ['required_with:lines', 'numeric', 'min:0'],
            'lines.*.credit' => ['required_with:lines', 'numeric', 'min:0'],
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

    private function routeJournalEntry(): ?JournalEntry
    {
        $journalEntry = $this->route('journal_entry') ?? $this->route('journalEntry');

        if ($journalEntry instanceof JournalEntry) {
            return $journalEntry;
        }

        return $journalEntry === null ? null : JournalEntry::query()->find($journalEntry);
    }

    private function validateLineTotals(Validator $validator): void
    {
        if (! $this->has('lines') || $validator->errors()->has('lines') || ! is_array($this->input('lines'))) {
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
