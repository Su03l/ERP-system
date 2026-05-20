<?php

namespace App\Http\Controllers;

use App\DTOs\KpiDateRange;
use App\DTOs\KpiResult;
use App\Models\DashboardWidget;
use App\Services\KpiRegistry;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dynamic ERP dashboard with resolved widgets.
     */
    public function index(Request $request, TenantContext $tenantContext): View
    {
        $company = $tenantContext->company();

        if ($company === null) {
            abort(403, 'Unauthorized company context.');
        }

        // 1. Resolve selected date range
        $start = $request->input('date_from', now()->startOfMonth()->toDateString());
        $end = $request->input('date_to', now()->endOfMonth()->toDateString());

        try {
            $dateRange = KpiDateRange::fromDates($start, $end);
        } catch (\InvalidArgumentException $e) {
            $dateRange = KpiDateRange::fromDates(
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString()
            );
        }

        // 2. Fetch widgets for the current company
        $widgets = DashboardWidget::query()->forCurrentCompany()->get();

        // 3. Seed default widgets if none exist yet
        if ($widgets->isEmpty()) {
            $defaultWidgets = [
                [
                    'widget_key' => 'hr.total_employees',
                    'module' => 'hr',
                    'title_ar' => 'إجمالي الموظفين',
                    'title_en' => 'Total Employees',
                    'type' => 'kpi',
                    'resolver' => 'hr.total_employees',
                    'required_permission' => 'employees.view',
                    'default_size' => 'small',
                    'metadata' => [],
                ],
                [
                    'widget_key' => 'attendance.attendance_rate',
                    'module' => 'attendance',
                    'title_ar' => 'معدل الحضور اليومي',
                    'title_en' => 'Attendance Rate',
                    'type' => 'kpi',
                    'resolver' => 'attendance.attendance_rate',
                    'required_permission' => 'attendance.view',
                    'default_size' => 'small',
                    'metadata' => [],
                ],
                [
                    'widget_key' => 'accounting.net_profit',
                    'module' => 'accounting',
                    'title_ar' => 'صافي الأرباح',
                    'title_en' => 'Net Profit',
                    'type' => 'kpi',
                    'resolver' => 'accounting.net_profit',
                    'required_permission' => 'financial_reports.view',
                    'default_size' => 'small',
                    'metadata' => [],
                ],
                [
                    'widget_key' => 'leave.pending_requests',
                    'module' => 'leave',
                    'title_ar' => 'طلبات الإجازة المعلقة',
                    'title_en' => 'Pending Leave Requests',
                    'type' => 'kpi',
                    'resolver' => 'leave.pending_requests',
                    'required_permission' => 'leave_requests.view',
                    'default_size' => 'small',
                    'metadata' => [],
                ],
                [
                    'widget_key' => 'hr.employees_by_department',
                    'module' => 'hr',
                    'title_ar' => 'توزيع الموظفين على الأقسام',
                    'title_en' => 'Employees by Department',
                    'type' => 'chart',
                    'resolver' => 'hr.employees_by_department',
                    'required_permission' => 'employees.view',
                    'default_size' => 'medium',
                    'metadata' => [],
                ],
                [
                    'widget_key' => 'payroll.by_department',
                    'module' => 'payroll',
                    'title_ar' => 'الرواتب والمستحقات حسب القسم',
                    'title_en' => 'Payroll by Department',
                    'type' => 'table',
                    'resolver' => 'payroll.by_department',
                    'required_permission' => 'payroll_runs.view',
                    'default_size' => 'medium',
                    'metadata' => [],
                ],
            ];

            foreach ($defaultWidgets as $data) {
                $widget = new DashboardWidget($data);
                $widget->company_id = $company->id;
                $widget->save();
            }

            $widgets = DashboardWidget::query()->forCurrentCompany()->get();
        }

        // 4. Filter widgets based on user permissions
        $user = $request->user();
        $filteredWidgets = $widgets->filter(function (DashboardWidget $widget) use ($user): bool {
            if ($widget->required_permission !== null && $widget->required_permission !== '') {
                return $user->can($widget->required_permission);
            }

            return true;
        });

        // 5. Resolve live KPI values
        $registry = KpiRegistry::default();
        $resolvedData = [];

        foreach ($filteredWidgets as $widget) {
            try {
                $result = $registry->resolve($widget->resolver, $company, $dateRange);
                $resolvedData[$widget->id] = $result;
            } catch (\Exception $e) {
                // Return a fallback result if resolver is missing or failing
                $resolvedData[$widget->id] = new KpiResult(
                    key: $widget->resolver,
                    label: app()->getLocale() === 'ar' ? $widget->title_ar : $widget->title_en,
                    value: null,
                    category: $widget->module,
                    dateRange: $dateRange,
                    formattedValue: null,
                    comparisonValue: null,
                    trend: null,
                    unit: null,
                    metadata: null
                );
            }
        }

        return view('dashboard', [
            'widgets' => $filteredWidgets,
            'resolvedData' => $resolvedData,
            'dateFrom' => $dateRange->start->toDateString(),
            'dateTo' => $dateRange->end->toDateString(),
        ]);
    }
}
