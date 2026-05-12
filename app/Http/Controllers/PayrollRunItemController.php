<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexPayrollRunItemRequest;
use App\Http\Resources\PayrollRunItemResource;
use App\Http\Resources\PayslipResource;
use App\Models\PayrollRunItem;
use App\Services\PayslipDataService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class PayrollRunItemController extends Controller
{
    public function index(IndexPayrollRunItemRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', PayrollRunItem::class);
        $filters = $request->validated();

        $items = PayrollRunItem::query()
            ->forCurrentCompany()
            ->with(['employee', 'payrollRun.payrollPeriod'])
            ->when($filters['payroll_run_id'] ?? null, fn ($query, int $runId) => $query->where('payroll_run_id', $runId))
            ->when($filters['employee_id'] ?? null, fn ($query, int $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate();

        return PayrollRunItemResource::collection($items);
    }

    public function show(PayrollRunItem $payrollRunItem): PayrollRunItemResource
    {
        Gate::authorize('view', $payrollRunItem);

        return PayrollRunItemResource::make($payrollRunItem->load(['employee', 'payrollRun.payrollPeriod', 'components']));
    }

    public function payslip(PayrollRunItem $payrollRunItem, PayslipDataService $payslipDataService): PayslipResource
    {
        Gate::authorize('view', $payrollRunItem);

        return PayslipResource::make($payslipDataService->make($payrollRunItem));
    }
}
