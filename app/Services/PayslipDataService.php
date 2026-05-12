<?php

namespace App\Services;

use App\Models\PayrollRunItem;
use Illuminate\Support\Facades\App;

class PayslipDataService
{
    /**
     * @return array<string, mixed>
     */
    public function make(PayrollRunItem $item, ?string $locale = null): array
    {
        $locale ??= App::getLocale();
        $item->loadMissing(['employee.department', 'employee.jobTitle', 'payrollRun.payrollPeriod', 'company', 'components']);
        $employee = $item->employee;
        $period = $item->payrollRun->payrollPeriod;

        return [
            'labels' => [
                'employee' => __('payroll.payslip.employee', locale: $locale),
                'period' => __('payroll.payslip.period', locale: $locale),
                'basic_salary' => __('payroll.payslip.basic_salary', locale: $locale),
                'allowances' => __('payroll.payslip.allowances', locale: $locale),
                'deductions' => __('payroll.payslip.deductions', locale: $locale),
                'gross_salary' => __('payroll.payslip.gross_salary', locale: $locale),
                'net_salary' => __('payroll.payslip.net_salary', locale: $locale),
            ],
            'company' => [
                'id' => $item->company_id,
                'name' => $item->company?->name,
                'legal_name' => $item->company?->legal_name,
                'currency' => $item->company?->currency,
            ],
            'employee' => [
                'id' => $employee?->id,
                'employee_number' => $employee?->employee_number,
                'name_ar' => trim((string) $employee?->first_name_ar.' '.(string) $employee?->last_name_ar),
                'name_en' => trim((string) $employee?->first_name_en.' '.(string) $employee?->last_name_en) ?: null,
                'department' => $employee?->department?->name_ar,
                'job_title' => $employee?->jobTitle?->name_ar,
            ],
            'period' => [
                'id' => $period?->id,
                'name_ar' => $period?->name_ar,
                'name_en' => $period?->name_en,
                'starts_on' => $period?->starts_on?->toDateString(),
                'ends_on' => $period?->ends_on?->toDateString(),
                'pay_date' => $period?->pay_date?->toDateString(),
            ],
            'earnings' => [
                'basic_salary' => $item->basic_salary,
                'gross_salary' => $item->gross_salary,
                'total_allowances' => $item->total_allowances,
                'overtime_amount' => $item->overtime_amount,
            ],
            'deductions' => [
                'total_deductions' => $item->total_deductions,
                'attendance_deduction' => $item->attendance_deduction,
                'leave_deduction' => $item->leave_deduction,
            ],
            'net_salary' => $item->net_salary,
            'components' => $item->components->map(fn ($component): array => [
                'type' => $component->type?->value ?? $component->type,
                'name_ar' => $component->name_ar,
                'name_en' => $component->name_en,
                'amount' => $component->amount,
            ])->values()->all(),
        ];
    }
}
