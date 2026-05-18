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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class PayrollRunController extends Controller
{
    public function index(IndexPayrollRunRequest $request): AnonymousResourceCollection
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

        return PayrollRunResource::collection($runs);
    }

    public function store(GeneratePayrollRunRequest $request, GeneratePayrollRun $action): PayrollRunResource
    {
        Gate::authorize('generate', PayrollRun::class);
        $period = PayrollPeriod::query()->forCurrentCompany()->findOrFail((int) $request->validated('payroll_period_id'));

        return PayrollRunResource::make($action->handle($period, $request->validated(), $request->user())->load(['payrollPeriod', 'items']));
    }

    public function show(PayrollRun $payrollRun): PayrollRunResource
    {
        Gate::authorize('view', $payrollRun);

        return PayrollRunResource::make($payrollRun->load(['payrollPeriod', 'items.employee']));
    }

    public function update(Request $request, PayrollRun $payrollRun): never
    {
        abort(405);
    }

    public function destroy(PayrollRun $payrollRun): never
    {
        abort(405);
    }

    public function approve(Request $request, PayrollRun $payrollRun, ApprovePayrollRun $action): PayrollRunResource
    {
        Gate::authorize('approve', $payrollRun);

        return PayrollRunResource::make($action->handle($payrollRun, $request->user(), $request->string('comment')->toString())->load(['payrollPeriod']));
    }

    public function reject(Request $request, PayrollRun $payrollRun, RejectPayrollRun $action): PayrollRunResource
    {
        Gate::authorize('reject', $payrollRun);

        return PayrollRunResource::make($action->handle($payrollRun, $request->user(), $request->string('reason')->toString())->load(['payrollPeriod']));
    }
}
