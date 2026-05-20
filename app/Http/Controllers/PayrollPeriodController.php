<?php

namespace App\Http\Controllers;

use App\Actions\CreatePayrollPeriod;
use App\Actions\UpdatePayrollPeriod;
use App\Http\Requests\IndexPayrollPeriodRequest;
use App\Http\Requests\StorePayrollPeriodRequest;
use App\Http\Requests\UpdatePayrollPeriodRequest;
use App\Http\Resources\PayrollPeriodResource;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Gate;

class PayrollPeriodController extends Controller
{
    public function index(IndexPayrollPeriodRequest $request)
    {
        Gate::authorize('viewAny', PayrollPeriod::class);
        $filters = $request->validated();

        $periods = PayrollPeriod::query()
            ->forCurrentCompany()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->whereDate('ends_on', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, string $to) => $query->whereDate('starts_on', '<=', $to))
            ->latest('id')
            ->paginate();

        if ($request->expectsJson()) {
            return PayrollPeriodResource::collection($periods);
        }

        return view('payroll-periods.index', compact('periods'));
    }

    public function create()
    {
        Gate::authorize('create', PayrollPeriod::class);

        return view('payroll-periods.create');
    }

    public function store(StorePayrollPeriodRequest $request, CreatePayrollPeriod $action)
    {
        $period = $action->handle($request->validated(), $request->user());

        if ($request->expectsJson()) {
            return PayrollPeriodResource::make($period);
        }

        return redirect()->route('payroll-periods.index')->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء الفترة بنجاح.' : 'Payroll period created.');
    }

    public function show(PayrollPeriod $payrollPeriod)
    {
        Gate::authorize('view', $payrollPeriod);

        if (request()->expectsJson()) {
            return PayrollPeriodResource::make($payrollPeriod);
        }

        return redirect()->route('payroll-periods.edit', $payrollPeriod->id);
    }

    public function edit(PayrollPeriod $payrollPeriod)
    {
        Gate::authorize('update', $payrollPeriod);

        return view('payroll-periods.edit', compact('payrollPeriod'));
    }

    public function update(UpdatePayrollPeriodRequest $request, PayrollPeriod $payrollPeriod, UpdatePayrollPeriod $action)
    {
        $result = $action->handle($payrollPeriod, $request->validated(), $request->user());

        if ($request->expectsJson()) {
            return PayrollPeriodResource::make($result);
        }

        return redirect()->route('payroll-periods.index')->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الفترة بنجاح.' : 'Payroll period updated.');
    }

    public function destroy(PayrollPeriod $payrollPeriod): never
    {
        abort(405);
    }
}
