<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddOnController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\CompanyAddOnController;
use App\Http\Controllers\CompanySubscriptionController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSalaryPackageController;
use App\Http\Controllers\JobTitleController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\PayrollRunController;
use App\Http\Controllers\PayrollRunItemController;
use App\Http\Controllers\PayrollSettingController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\SubscriptionInvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('job-titles', JobTitleController::class);

    Route::post('attendance-records/clock-in', [AttendanceRecordController::class, 'clockIn'])->name('attendance-records.clock-in');
    Route::post('attendance-records/clock-out', [AttendanceRecordController::class, 'clockOut'])->name('attendance-records.clock-out');
    Route::apiResource('attendance-records', AttendanceRecordController::class);

    Route::post('leave-requests/{leave_request}/submit', [LeaveRequestController::class, 'submit'])->name('leave-requests.submit');
    Route::post('leave-requests/{leave_request}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('leave-requests/{leave_request}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    Route::post('leave-requests/{leave_request}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
    Route::post('leave-requests/{leave_request}/return', [LeaveRequestController::class, 'return'])->name('leave-requests.return');
    Route::apiResource('leave-types', LeaveTypeController::class);
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
});
