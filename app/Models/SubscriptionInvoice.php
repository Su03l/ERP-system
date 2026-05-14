<?php

namespace App\Models;

use App\Enums\SubscriptionInvoiceStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\SubscriptionInvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'subscription_id',
    'invoice_number',
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
    'metadata',
])]
class SubscriptionInvoice extends Model
{
    /** @use HasFactory<SubscriptionInvoiceFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the subscription this platform invoice belongs to.
     *
     * @return BelongsTo<CompanySubscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CompanySubscription::class, 'subscription_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'status' => SubscriptionInvoiceStatus::class,
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
