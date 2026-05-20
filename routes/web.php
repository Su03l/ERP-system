<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddOnController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\ApprovalInboxController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ChartDataController;
use App\Http\Controllers\CompanyAddOnController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\CompanySubscriptionController;
use App\Http\Controllers\CrmContactController;
use App\Http\Controllers\CrmLeadController;
use App\Http\Controllers\DashboardWidgetController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSalaryPackageController;
use App\Http\Controllers\HrImportExportController;
use App\Http\Controllers\JobTitleController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\PayrollRunController;
use App\Http\Controllers\PayrollRunItemController;
use App\Http\Controllers\PayrollSettingController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\ProjectTimeLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\SecuritySettingController;
use App\Http\Controllers\SubscriptionInvoiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSessionController;
use App\Http\Controllers\WebhookDeliveryController;
use App\Http\Controllers\WebhookEndpointController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\AttendanceDashboardController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\HrmsDashboardController;
use App\Http\Controllers\LeaveDashboardController;
use App\Http\Controllers\NotificationController;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('/verify-email', [VerifyEmailController::class, 'show'])->name('verification.notice');
Route::post('/verify-email/resend', [VerifyEmailController::class, 'resend'])->name('verification.send');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('hrms/dashboard', [HrmsDashboardController::class, 'index'])->name('hrms.dashboard');
    Route::resource('employees', EmployeeController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('job-titles', JobTitleController::class);

    Route::resource('employee-documents', EmployeeDocumentController::class);
    Route::get('hr-import-export', [HrImportExportController::class, 'index'])->name('hr-import-export.index');
    Route::get('attendance/dashboard', [AttendanceDashboardController::class, 'index'])->name('attendance.dashboard');
    Route::get('leave/dashboard', [LeaveDashboardController::class, 'index'])->name('leave.dashboard');
    Route::get('attendance-records/self-service', [AttendanceRecordController::class, 'selfService'])->name('attendance.self-service');
    Route::post('attendance-records/clock-in', [AttendanceRecordController::class, 'clockIn'])->name('attendance-records.clock-in');
    Route::post('attendance-records/clock-out', [AttendanceRecordController::class, 'clockOut'])->name('attendance-records.clock-out');
    Route::resource('attendance-records', AttendanceRecordController::class);

    Route::post('leave-requests/{leave_request}/submit', [LeaveRequestController::class, 'submit'])->name('leave-requests.submit');
    Route::post('leave-requests/{leave_request}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('leave-requests/{leave_request}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    Route::post('leave-requests/{leave_request}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
    Route::post('leave-requests/{leave_request}/return', [LeaveRequestController::class, 'return'])->name('leave-requests.return');
    Route::resource('leave-types', LeaveTypeController::class);
    Route::apiResource('leave-requests', LeaveRequestController::class);
    Route::apiResource('leave-balances', LeaveBalanceController::class)->only(['index', 'show', 'update']);

    Route::apiResource('payroll-settings', PayrollSettingController::class)->only(['index', 'show', 'update']);
    Route::apiResource('salary-components', SalaryComponentController::class)->except(['destroy']);
    Route::apiResource('salary-packages', EmployeeSalaryPackageController::class)->except(['destroy'])->parameters(['salary-packages' => 'employeeSalaryPackage']);
    Route::post('payroll-runs/{payroll_run}/approve', [PayrollRunController::class, 'approve'])->name('payroll-runs.approve');
    Route::post('payroll-runs/{payroll_run}/reject', [PayrollRunController::class, 'reject'])->name('payroll-runs.reject');
    Route::apiResource('payroll-periods', PayrollPeriodController::class)->except(['destroy']);
    Route::apiResource('payroll-runs', PayrollRunController::class)->only(['index', 'store', 'show']);
    Route::get('payroll-run-items/{payroll_run_item}/payslip', [PayrollRunItemController::class, 'payslip'])->name('payroll-run-items.payslip');
    Route::apiResource('payroll-run-items', PayrollRunItemController::class)->only(['index', 'show']);

    Route::apiResource('accounts', AccountController::class);
    Route::post('crm-leads/{crm_lead}/convert', [CrmLeadController::class, 'convert'])->name('crm-leads.convert');
    Route::apiResource('crm-leads', CrmLeadController::class);
    Route::apiResource('crm-contacts', CrmContactController::class);
    Route::post('project-tasks/{project_task}/complete', [ProjectTaskController::class, 'complete'])->name('project-tasks.complete');
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('project-tasks', ProjectTaskController::class);
    Route::apiResource('project-time-logs', ProjectTimeLogController::class);
    Route::apiResource('asset-categories', AssetCategoryController::class);
    Route::apiResource('assets', AssetController::class);
    Route::post('journal-entries/{journal_entry}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
    Route::post('journal-entries/{journal_entry}/approve', [JournalEntryController::class, 'approve'])->name('journal-entries.approve');
    Route::post('journal-entries/{journal_entry}/reject', [JournalEntryController::class, 'reject'])->name('journal-entries.reject');
    Route::post('journal-entries/{journal_entry}/reverse', [JournalEntryController::class, 'reverse'])->name('journal-entries.reverse');
    Route::apiResource('journal-entries', JournalEntryController::class)->except(['destroy']);

    Route::post('company-subscriptions/{company_subscription}/cancel', [CompanySubscriptionController::class, 'cancel'])->name('company-subscriptions.cancel');
    Route::post('subscription-invoices/{subscription_invoice}/mark-paid', [SubscriptionInvoiceController::class, 'markPaid'])->name('subscription-invoices.mark-paid');
    Route::post('subscription-invoices/{subscription_invoice}/cancel', [SubscriptionInvoiceController::class, 'cancel'])->name('subscription-invoices.cancel');
    Route::post('company-add-ons/{company_add_on}/deactivate', [CompanyAddOnController::class, 'deactivate'])->name('company-add-ons.deactivate');
    Route::apiResource('plans', PlanController::class);
    Route::apiResource('company-subscriptions', CompanySubscriptionController::class)->except(['destroy']);
    Route::apiResource('subscription-invoices', SubscriptionInvoiceController::class)->except(['destroy']);
    Route::apiResource('add-ons', AddOnController::class);
    Route::apiResource('company-add-ons', CompanyAddOnController::class)->except(['destroy']);

    Route::get('analytics/kpis', [KpiController::class, 'index'])->name('analytics.kpis.index');
    Route::post('analytics/kpis/query', [KpiController::class, 'query'])->name('analytics.kpis.query');
    Route::post('analytics/charts', [ChartDataController::class, 'store'])->name('analytics.charts.store');
    Route::get('analytics/reports', [ReportController::class, 'index'])->name('analytics.reports.index');
    Route::post('analytics/reports/execute', [ReportController::class, 'execute'])->name('analytics.reports.execute');
    Route::post('analytics/reports/export', [ReportController::class, 'export'])->name('analytics.reports.export');
    Route::apiResource('dashboard-widgets', DashboardWidgetController::class);

    Route::apiResource('security-settings', SecuritySettingController::class)->only(['index', 'show', 'update']);
    Route::apiResource('audit-logs', AuditLogController::class)->only(['index']);
    Route::apiResource('api-tokens', ApiTokenController::class)->only(['index', 'store', 'destroy'])->parameters(['api-tokens' => 'company_api_token']);
    Route::apiResource('webhook-endpoints', WebhookEndpointController::class);
    Route::apiResource('webhook-deliveries', WebhookDeliveryController::class)->only(['index', 'show']);
    Route::apiResource('user-sessions', UserSessionController::class)->only(['index', 'destroy']);

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    // Web UIs for Settings, Profile, Roles, Users and Approvals
    Route::get('/company-settings', [CompanySettingsController::class, 'index'])->name('company-settings.index');
    Route::post('/company-settings', [CompanySettingsController::class, 'update'])->name('company-settings.update');
    Route::post('/company-settings/security', [CompanySettingsController::class, 'updateSecurity'])->name('company-settings.security');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile/sessions/{userSession}', [ProfileController::class, 'revokeSession'])->name('profile.sessions.revoke');
    Route::post('/profile/tokens', [ProfileController::class, 'createToken'])->name('profile.tokens.create');
    Route::delete('/profile/tokens/{companyApiToken}', [ProfileController::class, 'revokeToken'])->name('profile.tokens.revoke');

    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('users', UserController::class);

    Route::get('/approval-inbox', [ApprovalInboxController::class, 'index'])->name('approvals.index');
    Route::get('/approval-inbox/{instance}', [ApprovalInboxController::class, 'show'])->name('approvals.show');
    Route::post('/approval-inbox/{instance}/decide', [ApprovalInboxController::class, 'decide'])->name('approvals.decide');

    Route::get('global-search', [GlobalSearchController::class, 'search'])->name('global-search');
});
