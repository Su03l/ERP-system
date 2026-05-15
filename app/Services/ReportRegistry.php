<?php

namespace App\Services;

use App\Contracts\ReportResolver;
use App\DTOs\ReportDefinition;
use InvalidArgumentException;

class ReportRegistry
{
    /**
     * @param  iterable<ReportResolver|ReportDefinition>  $reports
     */
    public function __construct(private iterable $reports = []) {}

    public static function default(): self
    {
        return new self([
            new ReportDefinition(
                key: 'hr.employees',
                module: 'hr',
                nameAr: 'تقرير الموظفين',
                nameEn: 'Employee report',
                descriptionAr: 'بيانات الموظفين القابلة للتصفية والتصدير.',
                descriptionEn: 'Filterable and export-ready employee data.',
                requiredPermission: 'employees.view',
                resolverClass: EmployeeExportQuery::class,
                supportedFilters: ['department_id', 'employee_id', 'status', 'date_from', 'date_to', 'search'],
                supportedExports: ['pdf', 'excel', 'csv'],
            ),
            new ReportDefinition(
                key: 'attendance.records',
                module: 'attendance',
                nameAr: 'تقرير الحضور',
                nameEn: 'Attendance report',
                descriptionAr: 'سجلات الحضور والانصراف حسب الموظف والقسم والتاريخ.',
                descriptionEn: 'Attendance records by employee, department, and date range.',
                requiredPermission: 'attendance.export',
                resolverClass: AttendanceExportQuery::class,
                supportedFilters: ['department_id', 'employee_id', 'status', 'date_from', 'date_to'],
                supportedExports: ['pdf', 'excel', 'csv'],
            ),
            new ReportDefinition(
                key: 'leave.requests',
                module: 'leave',
                nameAr: 'تقرير الإجازات',
                nameEn: 'Leave report',
                descriptionAr: 'طلبات وأرصدة الإجازات القابلة للتصفية والتصدير.',
                descriptionEn: 'Filterable and export-ready leave requests and balances.',
                requiredPermission: 'leave_requests.view',
                resolverClass: LeaveRequestIndexQuery::class,
                supportedFilters: ['employee_id', 'status', 'date_from', 'date_to', 'leave_type_id'],
                supportedExports: ['pdf', 'excel', 'csv'],
            ),
            new ReportDefinition(
                key: 'payroll.runs',
                module: 'payroll',
                nameAr: 'تقرير الرواتب',
                nameEn: 'Payroll report',
                descriptionAr: 'ملخصات ومسيرات الرواتب مع حماية صلاحيات الرواتب.',
                descriptionEn: 'Payroll summaries and run items protected by payroll permissions.',
                requiredPermission: 'payroll_runs.export',
                resolverClass: PayrollExportService::class,
                supportedFilters: ['payroll_period_id', 'employee_id', 'status', 'date_from', 'date_to'],
                supportedExports: ['pdf', 'excel', 'csv'],
            ),
            new ReportDefinition(
                key: 'accounting.financial',
                module: 'accounting',
                nameAr: 'التقارير المالية',
                nameEn: 'Financial reports',
                descriptionAr: 'ميزان المراجعة والأستاذ العام وكشوف الحسابات.',
                descriptionEn: 'Trial balance, general ledger, and account statements.',
                requiredPermission: 'financial_reports.view',
                resolverClass: FinancialReportQueryService::class,
                supportedFilters: ['account_id', 'date_from', 'date_to', 'include_drafts'],
                supportedExports: ['pdf', 'excel', 'csv'],
            ),
            new ReportDefinition(
                key: 'assets.assets',
                module: 'assets',
                nameAr: 'تقرير الأصول',
                nameEn: 'Assets report',
                descriptionAr: 'بيانات الأصول والفئات والحيازة الحالية.',
                descriptionEn: 'Asset, category, and current custody export data.',
                requiredPermission: 'assets.export',
                resolverClass: AssetExportQuery::class,
                supportedFilters: ['category_id', 'status', 'employee_id', 'date_from', 'date_to', 'search'],
                supportedExports: ['pdf', 'excel', 'csv'],
            ),
            new ReportDefinition(
                key: 'documents.expiry',
                module: 'documents',
                nameAr: 'تقرير انتهاء المستندات',
                nameEn: 'Documents expiry report',
                descriptionAr: 'المستندات المنتهية أو القريبة من الانتهاء.',
                descriptionEn: 'Expired and soon-expiring documents.',
                requiredPermission: 'company_documents.view',
                resolverClass: DocumentExportQuery::class,
                supportedFilters: ['document_type', 'status', 'expiry_from', 'expiry_until'],
                supportedExports: ['pdf', 'excel', 'csv'],
            ),
            new ReportDefinition(
                key: 'projects.projects',
                module: 'projects',
                nameAr: 'تقرير المشاريع',
                nameEn: 'Projects report',
                descriptionAr: 'المشاريع والمهام وساعات العمل القابلة للتصدير.',
                descriptionEn: 'Export-ready projects, tasks, and time logs.',
                requiredPermission: 'projects.export',
                resolverClass: ProjectCrmExportQuery::class,
                supportedFilters: ['customer_id', 'employee_id', 'status', 'priority', 'date_from', 'date_to'],
                supportedExports: ['pdf', 'excel', 'csv'],
            ),
            new ReportDefinition(
                key: 'saas.revenue',
                module: 'saas',
                nameAr: 'تقرير إيرادات المنصة',
                nameEn: 'SaaS revenue report',
                descriptionAr: 'مؤشرات وفواتير الاشتراكات للمدير العام.',
                descriptionEn: 'Platform subscription invoices and revenue metrics for Super Admins.',
                requiredPermission: 'subscription_invoices.view',
                resolverClass: SaasExportService::class,
                supportedFilters: ['company_id', 'plan_id', 'status', 'date_from', 'date_to'],
                supportedExports: ['pdf', 'excel', 'csv'],
            ),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function available(): array
    {
        return collect($this->reports)
            ->map(fn (ReportResolver|ReportDefinition $report): array => $this->definitionFrom($report)->toArray())
            ->values()
            ->all();
    }

    public function definition(string $key): ReportDefinition
    {
        foreach ($this->reports as $report) {
            $definition = $this->definitionFrom($report);

            if ($definition->key === $key) {
                return $definition;
            }
        }

        throw new InvalidArgumentException("Report [{$key}] is not registered.");
    }

    public function supportsExport(string $key, string $format): bool
    {
        return $this->definition($key)->supportsExport($format);
    }

    private function definitionFrom(ReportResolver|ReportDefinition $report): ReportDefinition
    {
        return $report instanceof ReportDefinition ? $report : $report->definition();
    }
}
