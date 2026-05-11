<?php

namespace App\Models;

use App\Enums\SalaryComponentType;
use Database\Factories\PayrollRunItemComponentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'payroll_run_item_id',
    'salary_component_id',
    'type',
    'name_ar',
    'name_en',
    'amount',
    'metadata',
])]
class PayrollRunItemComponent extends Model
{
    /** @use HasFactory<PayrollRunItemComponentFactory> */
    use HasFactory;

    /**
     * Get the payroll run item this component belongs to.
     *
     * @return BelongsTo<PayrollRunItem, $this>
     */
    public function payrollRunItem(): BelongsTo
    {
        return $this->belongsTo(PayrollRunItem::class);
    }

    /**
     * Get the source salary component, when it still exists.
     *
     * @return BelongsTo<SalaryComponent, $this>
     */
    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SalaryComponentType::class,
            'amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }
}
