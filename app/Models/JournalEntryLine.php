<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\JournalEntryLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'journal_entry_id',
    'account_id',
    'description_ar',
    'description_en',
    'debit',
    'credit',
    'line_order',
    'metadata',
])]
class JournalEntryLine extends Model
{
    /** @use HasFactory<JournalEntryLineFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return BelongsTo<JournalEntry, $this>
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'line_order' => 'integer',
            'metadata' => 'array',
        ];
    }
}
