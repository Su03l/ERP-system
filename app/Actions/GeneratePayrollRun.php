<?php

namespace App\Actions;

use App\Enums\EmployeeStatus;
use App\Enums\PayrollRunItemStatus;
use App\Enums\PayrollRunStatus;
use App\Enums\SalaryPackageStatus;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\PayrollCalculationService;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GeneratePayrollRun
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly PayrollCalculationService $payrollCalculationService,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array{payroll_period_id?: int, run_number?: string, employee_ids?: array<int, int>, allow_duplicate?: bool, metadata?: array<string, mixed>}  $data
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(PayrollPeriod $period, array $data = [], ?User $actor = null): PayrollRun
    {
        $actor = $this->resolveActor($actor);
        $companyId = $this->resolveCompanyId();

        $this->authorizePayrollRun($actor, $companyId);
        $this->ensurePeriodBelongsToCompany($period, $companyId);

        if (($data['allow_duplicate'] ?? false) !== true) {
            $this->ensureNoActiveRunExists($period);
        }

        $employees = $this->eligibleEmployees($period, $data['employee_ids'] ?? null);
        $runNumber = $data['run_number'] ?? $this->nextRunNumber($period);

        return DB::transaction(function () use ($actor, $companyId, $data, $employees, $period, $runNumber): PayrollRun {
            $payrollRun = PayrollRun::create([
                'company_id' => $companyId,
                'payroll_period_id' => $period->id,
                'run_number' => $runNumber,
                'status' => PayrollRunStatus::Generated,
                'generated_by' => $actor->id,
                'generated_at' => now(),
                'metadata' => $data['metadata'] ?? [],
            ]);

            $totals = [
                'total_employees' => 0,
                'gross_amount' => 0,
                'total_allowances' => 0,
                'total_deductions' => 0,
                'net_amount' => 0,
            ];

            foreach ($employees as $employee) {
                $calculation = $this->payrollCalculationService->calculate($employee, $period);
                $runItem = $payrollRun->items()->create([
                    'company_id' => $companyId,
                    'employee_id' => $employee->id,
                    'basic_salary' => $calculation['basic_salary'],
                    'gross_salary' => $calculation['gross_salary'],
                    'total_allowances' => $calculation['total_allowances'],
                    'total_deductions' => $calculation['total_deductions'],
                    'net_salary' => $calculation['net_salary'],
                    'attendance_deduction' => $calculation['attendance_deduction'],
                    'leave_deduction' => $calculation['leave_deduction'],
                    'overtime_amount' => $calculation['overtime_amount'],
                    'status' => PayrollRunItemStatus::Calculated,
                    'metadata' => $calculation['metadata'],
                ]);

                foreach ($calculation['components'] as $component) {
                    $runItem->components()->create($component);
                }

                $totals['total_employees']++;
                $totals['gross_amount'] += $this->toCents($calculation['gross_salary']);
                $totals['total_allowances'] += $this->toCents($calculation['total_allowances']);
                $totals['total_deductions'] += $this->toCents($calculation['total_deductions']);
                $totals['net_amount'] += $this->toCents($calculation['net_salary']);
            }

            $payrollRun->forceFill([
                'total_employees' => $totals['total_employees'],
                'gross_amount' => $this->money($totals['gross_amount']),
                'total_allowances' => $this->money($totals['total_allowances']),
                'total_deductions' => $this->money($totals['total_deductions']),
                'net_amount' => $this->money($totals['net_amount']),
            ])->save();

            $this->auditLogger->log(
                action: 'payroll_run.generated',
                auditable: $payrollRun,
                newValues: $payrollRun->refresh()->load('items.components')->attributesToArray(),
                metadata: [
                    'payroll_period_id' => $period->id,
                    'employee_count' => $totals['total_employees'],
                ],
                user: $actor,
                company: $companyId,
            );

            return $payrollRun->refresh()->load('items.components');
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function resolveActor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to generate payroll runs.');
        }

        return $actor;
    }

    /**
     * @throws AuthorizationException
     */
    private function resolveCompanyId(): int
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            throw new AuthorizationException('A current company is required to generate payroll runs.');
        }

        return $companyId;
    }

    /**
     * @throws AuthorizationException
     */
    private function authorizePayrollRun(User $actor, int $companyId): void
    {
        if (! $actor->hasPermission('payroll.run', $companyId)) {
            throw new AuthorizationException('You are not authorized to generate payroll runs.');
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function ensurePeriodBelongsToCompany(PayrollPeriod $period, int $companyId): void
    {
        if ($period->company_id !== $companyId) {
            throw new AuthorizationException('Payroll period does not belong to the current company.');
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureNoActiveRunExists(PayrollPeriod $period): void
    {
        $exists = PayrollRun::query()
            ->where('company_id', $period->company_id)
            ->where('payroll_period_id', $period->id)
            ->whereNot('status', PayrollRunStatus::Cancelled->value)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'payroll_period_id' => __('validation.custom.payroll_runs.duplicate'),
            ]);
        }
    }

    /**
     * @param  array<int, int>|null  $employeeIds
     * @return Collection<int, Employee>
     */
    private function eligibleEmployees(PayrollPeriod $period, ?array $employeeIds = null): Collection
    {
        return Employee::query()
            ->where('company_id', $period->company_id)
            ->where('employment_status', EmployeeStatus::Active->value)
            ->when($employeeIds !== null && $employeeIds !== [], fn ($query) => $query->whereKey($employeeIds))
            ->whereHas('salaryPackages', function ($query) use ($period): void {
                $query->where('status', SalaryPackageStatus::Active->value)
                    ->whereDate('effective_from', '<=', $period->ends_on)
                    ->where(function ($query) use ($period): void {
                        $query->whereNull('effective_to')
                            ->orWhereDate('effective_to', '>=', $period->starts_on);
                    });
            })
            ->orderBy('id')
            ->get();
    }

    private function nextRunNumber(PayrollPeriod $period): string
    {
        $next = PayrollRun::query()
            ->where('company_id', $period->company_id)
            ->where('payroll_period_id', $period->id)
            ->count() + 1;

        return sprintf('PAY-%s-%03d', $period->id, $next);
    }

    private function toCents(string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
