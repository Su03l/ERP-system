<?php

namespace App\Http\Requests;

use App\Enums\JournalEntrySource;
use App\Enums\JournalEntryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexJournalEntryRequest extends FormRequest
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
        $companyId = (int) ($this->user()?->company_id ?? 0);

        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', Rule::enum(JournalEntryStatus::class)],
            'account_id' => ['nullable', 'integer', Rule::exists('accounts', 'id')->where('company_id', $companyId)],
            'source' => ['nullable', Rule::enum(JournalEntrySource::class)],
            'journal_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
