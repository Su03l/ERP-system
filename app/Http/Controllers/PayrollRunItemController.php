<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexPayrollRunItemRequest;
use App\Http\Resources\PayrollRunItemResource;
use App\Http\Resources\PayslipResource;
use App\Models\PayrollRunItem;
use App\Services\PayslipDataService;
use Illuminate\Support\Facades\Gate;

class PayrollRunItemController extends Controller
{
    public function index(IndexPayrollRunItemRequest $request)
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

        if ($request->expectsJson()) {
            return PayrollRunItemResource::collection($items);
        }

        return view('payroll-run-items.index', compact('items'));
    }

    public function show(PayrollRunItem $payrollRunItem)
    {
        Gate::authorize('view', $payrollRunItem);
        $payrollRunItem->load(['company', 'employee.department', 'employee.jobTitle', 'payrollRun.payrollPeriod', 'components']);

        if (request()->expectsJson()) {
            return PayrollRunItemResource::make($payrollRunItem);
        }

        return view('payroll-run-items.show', compact('payrollRunItem'));
    }

    public function payslip(PayrollRunItem $payrollRunItem, PayslipDataService $payslipDataService)
    {
        Gate::authorize('view', $payrollRunItem);
        $payrollRunItem->load(['company', 'employee.department', 'employee.jobTitle', 'payrollRun.payrollPeriod', 'components']);
        $payslipData = $payslipDataService->make($payrollRunItem);

        if (request()->expectsJson()) {
            return PayslipResource::make($payslipData);
        }

        return view('payroll-run-items.payslip', compact('payrollRunItem', 'payslipData'));
    }
}
