<?php

namespace App\Models;

use App\Enums\JournalEntrySource;
use App\Enums\JournalEntryStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\JournalEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'company_id',
    'journal_number',
    'entry_date',
    'description_ar',
    'description_en',
    'source',
    'source_type',
    'source_id',
    'status',
    'posted_by',
    'posted_at',
    'approved_by',
    'approved_at',
    'workflow_instance_id',
    'metadata',
])]
class JournalEntry extends Model
{
    /** @use HasFactory<JournalEntryFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return HasMany<JournalEntryLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @return BelongsTo<WorkflowInstance, $this>
     */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function sourceRecord(): MorphTo
    {
        return $this->morphTo('source');
    }

    public function isBalanced(): bool
    {
        return $this->toCents($this->debitTotal()) === $this->toCents($this->creditTotal());
    }

    public function debitTotal(): string
    {
        return number_format((float) $this->lines()->sum('debit'), 2, '.', '');
    }

    public function creditTotal(): string
    {
        return number_format((float) $this->lines()->sum('credit'), 2, '.', '');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'source' => JournalEntrySource::class,
            'status' => JournalEntryStatus::class,
            'posted_at' => 'datetime',
            'approved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $journalEntry): void {
            if ($journalEntry->status !== JournalEntryStatus::Posted || ! $journalEntry->exists) {
                return;
            }

            if (! $journalEntry->isBalanced()) {
                throw ValidationException::withMessages([
                    'lines' => __('accounting.validation.journal_entries.unbalanced'),
                ]);
            }
        });
    }

    private function toCents(string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
