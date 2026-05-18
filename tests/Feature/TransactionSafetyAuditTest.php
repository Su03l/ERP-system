<?php

use App\Actions\ActivateSubscription;
use App\Actions\ApprovePurchaseInvoice;
use App\Actions\AssignAssetToEmployee;
use App\Actions\CancelPurchaseInvoice;
use App\Actions\CancelSalesInvoice;
use App\Actions\CancelSubscription;
use App\Actions\CreateSalaryPackage;
use App\Actions\ExpireSubscription;
use App\Actions\GeneratePayrollRun;
use App\Actions\IssueSalesInvoice;
use App\Actions\PostJournalEntry;
use App\Actions\RenewSubscription;
use App\Actions\ReturnAssetFromEmployee;
use App\Actions\StartTrialSubscription;
use App\Actions\UpdateSalaryPackage;
use App\Services\LeaveBalanceService;
use App\Services\SecurityExportService;
use App\Services\WorkflowExecutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps sensitive write operations wrapped in database transactions', function (string $class, string $method = 'handle') {
    $file = (new ReflectionClass($class))->getFileName();
    $contents = file_get_contents($file);

    expect($contents)->toContain('DB::transaction')
        ->and($contents)->toContain("function {$method}");
})->with([
    [GeneratePayrollRun::class],
    [CreateSalaryPackage::class],
    [UpdateSalaryPackage::class],
    [WorkflowExecutionService::class, 'start'],
    [WorkflowExecutionService::class, 'approve'],
    [LeaveBalanceService::class, 'deductOnApproval'],
    [LeaveBalanceService::class, 'restoreOnCancellation'],
    [PostJournalEntry::class],
    [IssueSalesInvoice::class],
    [CancelSalesInvoice::class],
    [ApprovePurchaseInvoice::class],
    [CancelPurchaseInvoice::class],
    [AssignAssetToEmployee::class],
    [ReturnAssetFromEmployee::class],
    [ActivateSubscription::class],
    [CancelSubscription::class],
    [RenewSubscription::class],
    [ExpireSubscription::class],
    [StartTrialSubscription::class],
]);

it('keeps sensitive export approval audit writes transactional', function () {
    $contents = file_get_contents((new ReflectionClass(SecurityExportService::class))->getFileName());

    expect($contents)->toContain('DB::transaction')
        ->and($contents)->toContain('sensitive_export.requested')
        ->and($contents)->toContain('sensitive_export.approval_required')
        ->and($contents)->not->toContain('catch (Throwable');
});
