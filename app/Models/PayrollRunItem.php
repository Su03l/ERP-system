<?php

namespace App\Models;

use App\Enums\PayrollRunItemStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PayrollRunItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'payroll_run_id',
    'employee_id',
    'basic_salary',
    'gross_salary',
    'total_allowances',
    'total_deductions',
    'net_salary',
    'attendance_deduction',
    'leave_deduction',
    'overtime_amount',
    'status',
    'metadata',
])]
class PayrollRunItem extends Model
{
    /** @use HasFactory<PayrollRunItemFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the payroll run this item belongs to.
     *
     * @return BelongsTo<PayrollRun, $this>
     */
    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    /**
     * Get the employee this payroll item belongs to.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the component snapshots attached to this payroll item.
     *
     * @return HasMany<PayrollRunItemComponent, $this>
     */
    public function components(): HasMany
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
            'basic_salary' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'total_allowances' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'attendance_deduction' => 'decimal:2',
            'leave_deduction' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'status' => PayrollRunItemStatus::class,
            'metadata' => 'array',
        ];
    }
}
