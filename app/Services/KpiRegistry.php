<?php

namespace App\Services;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Services\Kpis\Accounting\CashFlowKpi;
use App\Services\Kpis\Accounting\ExpensesKpi;
use App\Services\Kpis\Accounting\NetProfitKpi;
use App\Services\Kpis\Accounting\OverdueInvoicesKpi;
use App\Services\Kpis\Accounting\PayablesKpi;
use App\Services\Kpis\Accounting\ReceivablesKpi;
use App\Services\Kpis\Accounting\RevenueKpi;
use App\Services\Kpis\Attendance\AbsenceRateKpi;
use App\Services\Kpis\Attendance\AttendanceRateKpi;
use App\Services\Kpis\Attendance\LateRateKpi;
use App\Services\Kpis\Attendance\OvertimeTotalKpi;
use App\Services\Kpis\Hr\ActiveEmployeesKpi;
use App\Services\Kpis\Hr\DocumentsExpiringSoonKpi;
use App\Services\Kpis\Hr\EmployeesByDepartmentKpi;
use App\Services\Kpis\Hr\InactiveEmployeesKpi;
use App\Services\Kpis\Hr\NewHiresKpi;
use App\Services\Kpis\Hr\TotalEmployeesKpi;
use App\Services\Kpis\Leave\ApprovedLeaveDaysKpi;
use App\Services\Kpis\Leave\LeaveBalanceSummaryKpi;
use App\Services\Kpis\Leave\PendingLeaveRequestsKpi;
use App\Services\Kpis\Payroll\AverageSalaryKpi;
use App\Services\Kpis\Payroll\LatestPayrollRunStatusKpi;
use App\Services\Kpis\Payroll\OvertimeCostKpi;
use App\Services\Kpis\Payroll\PayrollByDepartmentKpi;
use App\Services\Kpis\Payroll\TotalAllowancesKpi;
use App\Services\Kpis\Payroll\TotalDeductionsKpi;
use App\Services\Kpis\Payroll\TotalPayrollCostKpi;
use App\Services\Kpis\Projects\ActiveProjectsKpi;
use App\Services\Kpis\Projects\BillableHoursKpi;
use App\Services\Kpis\Projects\CompletedProjectsKpi;
use App\Services\Kpis\Projects\LeadConversionKpi;
use App\Services\Kpis\Projects\LeadsByStatusKpi;
use App\Services\Kpis\Projects\OverdueTasksKpi;
use App\Services\Kpis\Projects\TotalLoggedHoursKpi;
use App\Services\Kpis\Saas\ActiveTenantsKpi;
use App\Services\Kpis\Saas\AddOnRevenueKpi;
use App\Services\Kpis\Saas\ExpiredSubscriptionsKpi;
use App\Services\Kpis\Saas\MrrKpi;
use App\Services\Kpis\Saas\OverdueSubscriptionInvoicesKpi;
use App\Services\Kpis\Saas\RevenueByPlanKpi;
use App\Services\Kpis\Saas\TrialTenantsKpi;
use App\Services\Kpis\Saas\UsageSummaryKpi;
use InvalidArgumentException;

class KpiRegistry
{
    /**
     * @param  iterable<KpiResolver>  $resolvers
     */
    public function __construct(private iterable $resolvers = []) {}

    public static function default(): self
    {
        return new self([
            app(TotalEmployeesKpi::class),
            app(ActiveEmployeesKpi::class),
            app(InactiveEmployeesKpi::class),
            app(NewHiresKpi::class),
            app(EmployeesByDepartmentKpi::class),
            app(DocumentsExpiringSoonKpi::class),
            app(AttendanceRateKpi::class),
            app(AbsenceRateKpi::class),
            app(LateRateKpi::class),
            app(OvertimeTotalKpi::class),
            app(PendingLeaveRequestsKpi::class),
            app(ApprovedLeaveDaysKpi::class),
            app(LeaveBalanceSummaryKpi::class),
            app(TotalPayrollCostKpi::class),
            app(AverageSalaryKpi::class),
            app(TotalAllowancesKpi::class),
            app(TotalDeductionsKpi::class),
            app(OvertimeCostKpi::class),
            app(PayrollByDepartmentKpi::class),
            app(LatestPayrollRunStatusKpi::class),
            app(RevenueKpi::class),
            app(ExpensesKpi::class),
            app(NetProfitKpi::class),
            app(ReceivablesKpi::class),
            app(PayablesKpi::class),
            app(CashFlowKpi::class),
            app(OverdueInvoicesKpi::class),
            app(ActiveProjectsKpi::class),
            app(CompletedProjectsKpi::class),
            app(OverdueTasksKpi::class),
            app(TotalLoggedHoursKpi::class),
            app(BillableHoursKpi::class),
            app(LeadsByStatusKpi::class),
            app(LeadConversionKpi::class),
            app(MrrKpi::class),
            app(ActiveTenantsKpi::class),
            app(TrialTenantsKpi::class),
            app(ExpiredSubscriptionsKpi::class),
            app(OverdueSubscriptionInvoicesKpi::class),
            app(RevenueByPlanKpi::class),
            app(AddOnRevenueKpi::class),
            app(UsageSummaryKpi::class),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function available(): array
    {
        return collect($this->resolvers)
            ->map(fn (KpiResolver $resolver): array => $resolver->definition()->toArray())
            ->values()
            ->all();
    }

    public function definition(string $key): KpiDefinition
    {
        return $this->resolver($key)->definition();
    }

    public function resolve(string $key, Company $company, KpiDateRange $dateRange): KpiResult
    {
        return $this->resolver($key)->resolve($company, $dateRange);
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<int, array<string, mixed>>
     */
    public function export(array $keys, Company $company, KpiDateRange $dateRange): array
    {
        return collect($keys)
            ->map(fn (string $key): array => $this->resolve($key, $company, $dateRange)->toArray())
            ->values()
            ->all();
    }

    private function resolver(string $key): KpiResolver
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->key() === $key) {
                return $resolver;
            }
        }

        throw new InvalidArgumentException("KPI resolver [{$key}] is not registered.");
    }
}
