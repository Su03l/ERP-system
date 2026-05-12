<?php

namespace App\Models;

use App\Enums\PaymentDirection;
use App\Enums\PaymentStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'company_id',
    'payable_type',
    'payable_id',
    'customer_id',
    'vendor_id',
    'payment_number',
    'payment_date',
    'direction',
    'method',
    'amount',
    'currency',
    'reference',
    'status',
    'notes_ar',
    'notes_en',
    'posted_journal_entry_id',
    'metadata',
])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use BelongsToCompany, HasFactory;

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<Vendor, $this> */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /** @return BelongsTo<JournalEntry, $this> */
    public function postedJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'posted_journal_entry_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'direction' => PaymentDirection::class,
            'amount' => 'decimal:2',
            'status' => PaymentStatus::class,
            'metadata' => 'array',
        ];
    }
}
