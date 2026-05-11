<?php

namespace App\Models;

use App\Enums\SalaryCalculationType;
use App\Enums\SalaryComponentStatus;
use App\Enums\SalaryComponentType;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\SalaryComponentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'name_ar',
    'name_en',
    'code',
    'type',
    'calculation_type',
    'default_amount',
    'default_percentage',
    'is_taxable',
    'is_recurring',
    'status',
    'metadata',
])]
class SalaryComponent extends Model
{
    /** @use HasFactory<SalaryComponentFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get salary package items using this salary component.
     *
     * @return HasMany<EmployeeSalaryPackageItem, $this>
     */
    public function salaryPackageItems(): HasMany
    {
        return $this->hasMany(EmployeeSalaryPackageItem::class);
    }

    /**
     * Get payroll run component snapshots linked to this component.
     *
     * @return HasMany<PayrollRunItemComponent, $this>
     */
    public function payrollRunItemComponents(): HasMany
    {
        return $this->hasMany(PayrollRunItemComponent::class);
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
            'calculation_type' => SalaryCalculationType::class,
            'default_amount' => 'decimal:2',
            'default_percentage' => 'decimal:2',
            'is_taxable' => 'boolean',
            'is_recurring' => 'boolean',
            'status' => SalaryComponentStatus::class,
            'metadata' => 'array',
        ];
    }
}
