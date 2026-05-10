<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\EmployeeSalaryPackageItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'employee_salary_package_id',
    'salary_component_id',
    'amount',
    'percentage',
])]
class EmployeeSalaryPackageItem extends Model
{
    /** @use HasFactory<EmployeeSalaryPackageItemFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the salary package this item belongs to.
     *
     * @return BelongsTo<EmployeeSalaryPackage, $this>
     */
    public function salaryPackage(): BelongsTo
    {
        return $this->belongsTo(EmployeeSalaryPackage::class, 'employee_salary_package_id');
    }

    /**
     * Get the salary component configured for this item.
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
            'amount' => 'decimal:2',
            'percentage' => 'decimal:2',
        ];
    }
}
