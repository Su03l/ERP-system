<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PurchaseInvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'vendor_id',
    'invoice_number',
    'vendor_invoice_number',
    'invoice_date',
    'due_date',
    'status',
    'subtotal',
    'tax_amount',
    'discount_amount',
    'total_amount',
    'paid_amount',
    'balance_due',
    'currency',
    'notes_ar',
    'notes_en',
    'posted_journal_entry_id',
    'metadata',
])]
class PurchaseInvoice extends Model
{
    /** @use HasFactory<PurchaseInvoiceFactory> */
    use BelongsToCompany, HasFactory;

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

    /** @return HasMany<PurchaseInvoiceLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceLine::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'status' => InvoiceStatus::class,
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'metadata' => 'array',
        ];
    }
}
