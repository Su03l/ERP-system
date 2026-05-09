<?php

namespace App\Services;

use App\Enums\EmployeeStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class HrDashboardMetrics
{
    public function __construct(
        private readonly EmployeeDocumentExpiryQuery $employeeDocumentExpiryQuery,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forCurrentCompany(?CarbonInterface $start = null, ?CarbonInterface $end = null, int $documentExpiryDays = 30): array
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            return $this->emptyMetrics($start, $end, $documentExpiryDays);
        }

        return $this->forCompany($companyId, $start, $end, $documentExpiryDays);
    }

    /**
     * @return array<string, mixed>
     */
    public function forCompany(Company|int $company, ?CarbonInterface $start = null, ?CarbonInterface $end = null, int $documentExpiryDays = 30): array
    {
        $companyId = $company instanceof Company ? $company->id : $company;
        [$rangeStart, $rangeEnd] = $this->dateRange($start, $end);
        $baseQuery = Employee::query()->forCompany($companyId);

        return [
            'total_employees' => $this->metric('total_employees', __('hr.metrics.total_employees'), (clone $baseQuery)->count()),
            'active_employees' => $this->metric('active_employees', __('hr.metrics.active_employees'), (clone $baseQuery)->where('employment_status', EmployeeStatus::Active->value)->count()),
            'inactive_employees' => $this->metric('inactive_employees', __('hr.metrics.inactive_employees'), (clone $baseQuery)->where('employment_status', EmployeeStatus::Inactive->value)->count()),
            'new_hires' => [
                ...$this->metric('new_hires', __('hr.metrics.new_hires'), (clone $baseQuery)->whereBetween('hire_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])->count()),
                'date_range' => [
                    'start' => $rangeStart->toDateString(),
                    'end' => $rangeEnd->toDateString(),
                ],
            ],
            'documents_expiring_soon' => [
                ...$this->metric('documents_expiring_soon', __('hr.metrics.documents_expiring_soon'), $this->employeeDocumentExpiryQuery->expiringWithin($documentExpiryDays, $companyId)->count()),
                'days' => max(0, $documentExpiryDays),
            ],
            'employees_by_department' => [
                'key' => 'employees_by_department',
                'label' => __('hr.metrics.employees_by_department'),
                'values' => $this->employeesByDepartment($companyId),
            ],
            'employees_by_status' => [
                'key' => 'employees_by_status',
                'label' => __('hr.metrics.employees_by_status'),
                'values' => $this->employeesByStatus($companyId),
            ],
        ];
    }

    /**
     * @return array<int, array{department_id: int|null, label: string, value: int}>
     */
    private function employeesByDepartment(int $companyId): array
    {
        return Employee::query()
            ->forCompany($companyId)
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->select('employees.department_id')
            ->selectRaw('COALESCE(departments.name_ar, departments.name_en, ?) as department_label', [__('hr.metrics.unassigned_department')])
            ->selectRaw('COUNT(*) as employees_count')
            ->groupBy('employees.department_id', 'departments.name_ar', 'departments.name_en')
            ->orderBy('department_label')
            ->get()
            ->map(fn (object $row): array => [
                'department_id' => $row->department_id === null ? null : (int) $row->department_id,
                'label' => (string) $row->department_label,
                'value' => (int) $row->employees_count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{status: string, label: string, value: int}>
     */
    private function employeesByStatus(int $companyId): array
    {
        return Employee::query()
            ->forCompany($companyId)
            ->select('employment_status')
            ->selectRaw('COUNT(*) as employees_count')
            ->groupBy('employment_status')
            ->orderBy('employment_status')
            ->get()
            ->map(function (object $row): array {
                $status = $row->employment_status instanceof EmployeeStatus
                    ? $row->employment_status->value
                    : (string) $row->employment_status;

                return [
                    'status' => $status,
                    'label' => EmployeeStatus::tryFrom($status)?->label() ?? $status,
                    'value' => (int) $row->employees_count,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function dateRange(?CarbonInterface $start, ?CarbonInterface $end): array
    {
        $rangeStart = $start === null
            ? CarbonImmutable::now()->startOfMonth()
            : CarbonImmutable::instance($start)->startOfDay();

        $rangeEnd = $end === null
            ? CarbonImmutable::now()->endOfMonth()
            : CarbonImmutable::instance($end)->endOfDay();

        return [$rangeStart, $rangeEnd];
    }

    /**
     * @return array{key: string, label: string, value: int}
     */
    private function metric(string $key, string $label, int $value): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyMetrics(?CarbonInterface $start, ?CarbonInterface $end, int $documentExpiryDays): array
    {
        [$rangeStart, $rangeEnd] = $this->dateRange($start, $end);

        return [
            'total_employees' => $this->metric('total_employees', __('hr.metrics.total_employees'), 0),
            'active_employees' => $this->metric('active_employees', __('hr.metrics.active_employees'), 0),
            'inactive_employees' => $this->metric('inactive_employees', __('hr.metrics.inactive_employees'), 0),
            'new_hires' => [
                ...$this->metric('new_hires', __('hr.metrics.new_hires'), 0),
                'date_range' => [
                    'start' => $rangeStart->toDateString(),
                    'end' => $rangeEnd->toDateString(),
                ],
            ],
            'documents_expiring_soon' => [
                ...$this->metric('documents_expiring_soon', __('hr.metrics.documents_expiring_soon'), 0),
                'days' => max(0, $documentExpiryDays),
            ],
            'employees_by_department' => [
                'key' => 'employees_by_department',
                'label' => __('hr.metrics.employees_by_department'),
                'values' => [],
            ],
            'employees_by_status' => [
                'key' => 'employees_by_status',
                'label' => __('hr.metrics.employees_by_status'),
                'values' => [],
            ],
        ];
    }
}
