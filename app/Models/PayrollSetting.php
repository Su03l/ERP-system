<?php

namespace App\Models;

use App\Enums\PayrollCycleType;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PayrollSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'payroll_cycle_type',
    'default_pay_day',
    'overtime_calculation_enabled',
    'absence_deduction_enabled',
    'late_deduction_enabled',
    'default_currency',
    'payslip_language',
    'payroll_approval_required',
    'metadata',
])]
class PayrollSetting extends Model
{
    /** @use HasFactory<PayrollSettingFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payroll_cycle_type' => PayrollCycleType::class,
            'default_pay_day' => 'integer',
            'overtime_calculation_enabled' => 'boolean',
            'absence_deduction_enabled' => 'boolean',
            'late_deduction_enabled' => 'boolean',
            'payroll_approval_required' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
