<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveRequestStatus;
use App\Enums\SalaryComponentType;
use App\Enums\SalaryPackageStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeSalaryPackage;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class PayrollCalculationService
{
    /**
     * @return array{
     *     employee_id: int,
     *     salary_package_id: int,
     *     basic_salary: string,
     *     gross_salary: string,
     *     total_allowances: string,
     *     total_deductions: string,
     *     net_salary: string,
     *     attendance_deduction: string,
     *     leave_deduction: string,
     *     overtime_amount: string,
     *     components: array<int, array{salary_component_id: int|null, type: string, name_ar: string, name_en: string|null, amount: string, metadata: array<string, mixed>}>,
     *     metadata: array<string, mixed>
     * }
     */
    public function calculate(Employee $employee, PayrollPeriod $period): array
    {
        $this->ensureSameCompany($employee, $period);

        $company = $employee->company;
        $salaryPackage = $this->activeSalaryPackage($employee, $period);
        $settings = $company->payrollSetting;
        $basicSalary = $this->toCents($salaryPackage->basic_salary);

        $componentRows = [];
        $allowances = 0;
        $deductions = 0;

        [$baseAllowanceRows, $baseAllowanceTotal] = $this->baseAllowanceRows($salaryPackage);
        $componentRows = [...$componentRows, ...$baseAllowanceRows];
        $allowances += $baseAllowanceTotal;

        foreach ($salaryPackage->items as $item) {
            $salaryComponent = $item->salaryComponent;

            if ($salaryComponent === null) {
                continue;
            }

            $amount = $this->componentAmount($item->amount, $item->percentage, $basicSalary);
            $componentRows[] = [
                'salary_component_id' => $salaryComponent->id,
                'type' => $salaryComponent->type->value,
                'name_ar' => $salaryComponent->name_ar,
                'name_en' => $salaryComponent->name_en,
                'amount' => $this->money($amount),
                'metadata' => [
                    'source' => 'salary_component',
                    'code' => $salaryComponent->code,
                ],
            ];

            if ($salaryComponent->type === SalaryComponentType::Allowance) {
                $allowances += $amount;
            } else {
                $deductions += $amount;
            }
        }

        $workingDays = max(1, $this->workingDaysInPeriod($company, $period));
        $dailyRate = intdiv($basicSalary, $workingDays);
        $minuteRate = intdiv($dailyRate, max(1, $this->scheduledDailyMinutes($company)));

        $attendanceDeduction = ($settings?->late_deduction_enabled ?? true)
            ? $this->attendanceDeduction($employee, $period, $dailyRate, $minuteRate)
            : 0;
        $leaveDeduction = $this->leaveDeduction($employee, $period, $dailyRate);
        $overtimeAmount = ($settings?->overtime_calculation_enabled ?? true)
            ? $this->overtimeAmount($employee, $period, $minuteRate, $company)
            : 0;

        if (($settings?->absence_deduction_enabled ?? true) === false) {
            $attendanceDeduction = $this->lateDeduction($employee, $period, $minuteRate);
        }

        $deductions += $attendanceDeduction + $leaveDeduction;
        $grossSalary = $basicSalary + $allowances + $overtimeAmount;
        $netSalary = max(0, $grossSalary - $deductions);

        return [
            'employee_id' => $employee->id,
            'salary_package_id' => $salaryPackage->id,
            'basic_salary' => $this->money($basicSalary),
            'gross_salary' => $this->money($grossSalary),
            'total_allowances' => $this->money($allowances),
            'total_deductions' => $this->money($deductions),
            'net_salary' => $this->money($netSalary),
            'attendance_deduction' => $this->money($attendanceDeduction),
            'leave_deduction' => $this->money($leaveDeduction),
            'overtime_amount' => $this->money($overtimeAmount),
            'components' => $componentRows,
            'metadata' => [
                'salary_package_id' => $salaryPackage->id,
                'working_days' => $workingDays,
                'scheduled_daily_minutes' => $this->scheduledDailyMinutes($company),
            ],
        ];
    }

    private function ensureSameCompany(Employee $employee, PayrollPeriod $period): void
    {
        if ($employee->company_id !== $period->company_id) {
            throw ValidationException::withMessages([
                'employee_id' => __('validation.exists', ['attribute' => __('validation.attributes.employee_id')]),
            ]);
        }
    }

    private function activeSalaryPackage(Employee $employee, PayrollPeriod $period): EmployeeSalaryPackage
    {
        $salaryPackage = EmployeeSalaryPackage::query()
            ->with('items.salaryComponent')
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->where('status', SalaryPackageStatus::Active->value)
            ->whereDate('effective_from', '<=', $period->ends_on)
            ->where(function ($query) use ($period): void {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $period->starts_on);
            })
            ->latest('effective_from')
            ->first();

        if (! $salaryPackage instanceof EmployeeSalaryPackage) {
            throw ValidationException::withMessages([
                'employee_id' => __('validation.custom.salary_packages.missing_active'),
            ]);
        }

        return $salaryPackage;
    }

    /**
     * @return array{0: array<int, array{salary_component_id: null, type: string, name_ar: string, name_en: string, amount: string, metadata: array<string, string>}>, 1: int}
     */
    private function baseAllowanceRows(EmployeeSalaryPackage $salaryPackage): array
    {
        $rows = [];
        $total = 0;

        foreach ([
            'housing_allowance' => ['payroll.components.housing_allowance_ar', 'payroll.components.housing_allowance_en'],
            'transportation_allowance' => ['payroll.components.transportation_allowance_ar', 'payroll.components.transportation_allowance_en'],
        ] as $column => [$nameAr, $nameEn]) {
            $amount = $this->toCents($salaryPackage->{$column});

            if ($amount <= 0) {
                continue;
            }

            $rows[] = [
                'salary_component_id' => null,
                'type' => SalaryComponentType::Allowance->value,
                'name_ar' => __($nameAr, [], 'ar'),
                'name_en' => __($nameEn, [], 'en'),
                'amount' => $this->money($amount),
                'metadata' => ['source' => $column],
            ];
            $total += $amount;
        }

        return [$rows, $total];
    }

    private function componentAmount(mixed $amount, mixed $percentage, int $basicSalary): int
    {
        if ($percentage !== null) {
            return (int) round($basicSalary * ((float) $percentage / 100));
        }

        return $this->toCents($amount);
    }

    private function attendanceDeduction(Employee $employee, PayrollPeriod $period, int $dailyRate, int $minuteRate): int
    {
        $absentDays = AttendanceRecord::query()
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$period->starts_on, $period->ends_on])
            ->where('status', AttendanceStatus::Absent->value)
            ->count();

        return ($absentDays * $dailyRate) + $this->lateDeduction($employee, $period, $minuteRate);
    }

    private function lateDeduction(Employee $employee, PayrollPeriod $period, int $minuteRate): int
    {
        $lateMinutes = (int) AttendanceRecord::query()
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$period->starts_on, $period->ends_on])
            ->sum('late_minutes');

        return $lateMinutes * $minuteRate;
    }

    private function leaveDeduction(Employee $employee, PayrollPeriod $period, int $dailyRate): int
    {
        $unpaidDays = LeaveRequest::query()
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->where('status', LeaveRequestStatus::Approved->value)
            ->whereDate('start_date', '<=', $period->ends_on)
            ->whereDate('end_date', '>=', $period->starts_on)
            ->whereHas('leaveType', fn ($query) => $query->where('is_paid', false))
            ->get()
            ->sum(fn (LeaveRequest $leaveRequest): float => (float) $leaveRequest->total_days);

        return (int) round($unpaidDays * $dailyRate);
    }

    private function overtimeAmount(Employee $employee, PayrollPeriod $period, int $minuteRate, Company $company): int
    {
        $overtimeMinutes = (int) AttendanceRecord::query()
            ->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$period->starts_on, $period->ends_on])
            ->sum('overtime_minutes');
        $multiplier = (float) data_get($company->settings, 'payroll.overtime_multiplier', 1.5);

        return (int) round($overtimeMinutes * $minuteRate * $multiplier);
    }

    private function workingDaysInPeriod(Company $company, PayrollPeriod $period): int
    {
        $workingDays = $this->workingDays($company);
        $current = CarbonImmutable::parse($period->starts_on)->startOfDay();
        $end = CarbonImmutable::parse($period->ends_on)->startOfDay();
        $count = 0;

        while ($current->lessThanOrEqualTo($end)) {
            if (in_array(strtolower($current->englishDayOfWeek), $workingDays, true)) {
                $count++;
            }

            $current = $current->addDay();
        }

        return $count;
    }

    /**
     * @return array<int, string>
     */
    private function workingDays(Company $company): array
    {
        $workingDays = data_get($company->settings, 'attendance.working_days', data_get($company->settings, 'working_days'));

        if (! is_array($workingDays) || $workingDays === []) {
            return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        }

        return collect($workingDays)
            ->map(fn (mixed $day): string => strtolower((string) $day))
            ->values()
            ->all();
    }

    private function scheduledDailyMinutes(Company $company): int
    {
        $start = $this->timeOnDate('2026-01-01', (string) data_get($company->settings, 'attendance.work_start_time', data_get($company->settings, 'work_start_time', '09:00')));
        $end = $this->timeOnDate('2026-01-01', (string) data_get($company->settings, 'attendance.work_end_time', data_get($company->settings, 'work_end_time', '17:00')));

        return (int) max(1, $start->diffInMinutes($end, false));
    }

    private function timeOnDate(string $date, string $time): CarbonImmutable
    {
        [$hour, $minute] = array_pad(explode(':', $time), 2, 0);

        return CarbonImmutable::parse($date)->setTime((int) $hour, (int) $minute);
    }

    private function toCents(mixed $amount): int
    {
        return (int) round(((float) ($amount ?? 0)) * 100);
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
