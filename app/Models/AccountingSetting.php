<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\AccountingSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'fiscal_year_start_month',
    'default_currency',
    'tax_enabled',
    'default_vat_rate',
    'invoice_numbering_prefix',
    'journal_numbering_prefix',
    'accounting_approval_required',
    'metadata',
])]
class AccountingSetting extends Model
{
    /** @use HasFactory<AccountingSettingFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fiscal_year_start_month' => 'integer',
            'tax_enabled' => 'boolean',
            'default_vat_rate' => 'decimal:2',
            'accounting_approval_required' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
