<?php

namespace App\Models;

use App\Enums\PayrollRunStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PayrollRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'payroll_period_id',
    'run_number',
    'status',
    'total_employees',
    'gross_amount',
    'total_allowances',
    'total_deductions',
    'net_amount',
    'generated_by',
    'generated_at',
    'approved_by',
    'approved_at',
    'workflow_instance_id',
    'metadata',
])]
class PayrollRun extends Model
{
    /** @use HasFactory<PayrollRunFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the payroll period this run belongs to.
     *
     * @return BelongsTo<PayrollPeriod, $this>
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * Get the user who generated this payroll run.
     *
     * @return BelongsTo<User, $this>
     */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get the user who approved this payroll run.
     *
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the workflow instance attached to this payroll run.
     *
     * @return BelongsTo<WorkflowInstance, $this>
     */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PayrollRunStatus::class,
            'total_employees' => 'integer',
            'gross_amount' => 'decimal:2',
            'total_allowances' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'generated_at' => 'datetime',
            'approved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
