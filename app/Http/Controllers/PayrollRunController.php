<?php

namespace App\Http\Controllers;

use App\Actions\ApprovePayrollRun;
use App\Actions\GeneratePayrollRun;
use App\Actions\RejectPayrollRun;
use App\Http\Requests\GeneratePayrollRunRequest;
use App\Http\Requests\IndexPayrollRunRequest;
use App\Http\Resources\PayrollRunResource;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PayrollRunController extends Controller
{
    public function index(IndexPayrollRunRequest $request)
    {
        Gate::authorize('viewAny', PayrollRun::class);
        $filters = $request->validated();

        $runs = PayrollRun::query()
            ->forCurrentCompany()
            ->with('payrollPeriod')
            ->when($filters['payroll_period_id'] ?? null, fn ($query, int $periodId) => $query->where('payroll_period_id', $periodId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate();

        if ($request->expectsJson()) {
            return PayrollRunResource::collection($runs);
        }

        return view('payroll-runs.index', compact('runs'));
    }

    public function create()
    {
        Gate::authorize('generate', PayrollRun::class);
        $periods = PayrollPeriod::forCurrentCompany()->where('status', 'open')->latest('id')->get();

        return view('payroll-runs.create', compact('periods'));
    }

    public function store(GeneratePayrollRunRequest $request, GeneratePayrollRun $action)
    {
        Gate::authorize('generate', PayrollRun::class);
        $period = PayrollPeriod::query()->forCurrentCompany()->findOrFail((int) $request->validated('payroll_period_id'));
        $run = $action->handle($period, $request->validated(), $request->user())->load(['payrollPeriod', 'items']);

        if ($request->expectsJson()) {
            return PayrollRunResource::make($run);
        }

        return redirect()->route('payroll-runs.show', $run->id)->with('success', app()->getLocale() === 'ar' ? 'تم تشغيل الرواتب بنجاح.' : 'Payroll run generated successfully.');
    }

    public function show(PayrollRun $payrollRun)
    {
        Gate::authorize('view', $payrollRun);

        if (request()->expectsJson()) {
            return PayrollRunResource::make($payrollRun->load(['payrollPeriod', 'items.employee']));
        }

        $payrollRun->load([
            'payrollPeriod',
            'items.employee',
            'generatedBy',
            'approvedBy',
            'workflowInstance.workflow.steps',
            'workflowInstance.actions.actedBy',
            'workflowInstance.actions.workflowStep',
            'workflowInstance.currentStep',
        ]);

        return view('payroll-runs.show', compact('payrollRun'));
    }

    public function update(Request $request, PayrollRun $payrollRun): never
    {
        abort(405);
    }

    public function destroy(PayrollRun $payrollRun): never
    {
        abort(405);
    }

    public function approve(Request $request, PayrollRun $payrollRun, ApprovePayrollRun $action)
    {
        Gate::authorize('approve', $payrollRun);
        $result = $action->handle($payrollRun, $request->user(), $request->string('comment')->toString())->load(['payrollPeriod']);

        if ($request->expectsJson()) {
            return PayrollRunResource::make($result);
        }

        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تمت الموافقة على تشغيل الرواتب.' : 'Payroll run approved.');
    }

    public function reject(Request $request, PayrollRun $payrollRun, RejectPayrollRun $action)
    {
        Gate::authorize('reject', $payrollRun);
        $result = $action->handle($payrollRun, $request->user(), $request->string('reason')->toString())->load(['payrollPeriod']);

        if ($request->expectsJson()) {
            return PayrollRunResource::make($result);
        }

        return redirect()->back()->with('success', app()->getLocale() === 'ar' ? 'تم رفض تشغيل الرواتب.' : 'Payroll run rejected.');
    }
}
