<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PurchaseInvoiceLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'purchase_invoice_id',
    'description_ar',
    'description_en',
    'quantity',
    'unit_price',
    'discount_amount',
    'tax_rate',
    'tax_amount',
    'line_total',
    'metadata',
])]
class PurchaseInvoiceLine extends Model
{
    /** @use HasFactory<PurchaseInvoiceLineFactory> */
    use BelongsToCompany, HasFactory;

    /** @return BelongsTo<PurchaseInvoice, $this> */
    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
            'metadata' => 'array',
        ];
    }
}
