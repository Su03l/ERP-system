<?php

namespace App\Http\Requests;

use App\Enums\JournalEntryStatus;
use App\Models\JournalEntry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PostJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $journalEntry = $this->routeJournalEntry();

        return $journalEntry !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $journalEntry->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comment' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $journalEntry = $this->routeJournalEntry();

                if ($journalEntry === null) {
                    return;
                }

                if (! in_array($journalEntry->status, [JournalEntryStatus::Draft, JournalEntryStatus::Approved], true)) {
                    $validator->errors()->add('status', __('accounting.validation.journal_entries.postable_status'));
                }

                if (! $journalEntry->isBalanced()) {
                    $validator->errors()->add('lines', __('accounting.validation.journal_entries.unbalanced'));
                }
            },
        ];
    }

    private function routeJournalEntry(): ?JournalEntry
    {
        $journalEntry = $this->route('journal_entry') ?? $this->route('journalEntry');

        if ($journalEntry instanceof JournalEntry) {
            return $journalEntry;
        }

        return $journalEntry === null ? null : JournalEntry::query()->find($journalEntry);
    }
}
