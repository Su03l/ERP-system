<?php

namespace App\Models;

use App\Enums\SalaryPackageStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\EmployeeSalaryPackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'employee_id',
    'basic_salary',
    'housing_allowance',
    'transportation_allowance',
    'effective_from',
    'effective_to',
    'status',
    'metadata',
])]
class EmployeeSalaryPackage extends Model
{
    /** @use HasFactory<EmployeeSalaryPackageFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the employee this salary package belongs to.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the dynamic salary component items for this package.
     *
     * @return HasMany<EmployeeSalaryPackageItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(EmployeeSalaryPackageItem::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'housing_allowance' => 'decimal:2',
            'transportation_allowance' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'status' => SalaryPackageStatus::class,
            'metadata' => 'array',
        ];
    }
}
