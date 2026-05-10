<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\LeaveBalanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'employee_id',
    'leave_type_id',
    'year',
    'opening_balance',
    'accrued_days',
    'used_days',
    'remaining_days',
    'metadata',
])]
class LeaveBalance extends Model
{
    /** @use HasFactory<LeaveBalanceFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the employee this leave balance belongs to.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the leave type this balance is tracked for.
     *
     * @return BelongsTo<LeaveType, $this>
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'opening_balance' => 'decimal:2',
            'accrued_days' => 'decimal:2',
            'used_days' => 'decimal:2',
            'remaining_days' => 'decimal:2',
            'metadata' => 'array',
        ];
    }
}
