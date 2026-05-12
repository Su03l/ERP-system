<?php

namespace App\Http\Controllers;

use App\Actions\CreatePayrollPeriod;
use App\Actions\UpdatePayrollPeriod;
use App\Http\Requests\IndexPayrollPeriodRequest;
use App\Http\Requests\StorePayrollPeriodRequest;
use App\Http\Requests\UpdatePayrollPeriodRequest;
use App\Http\Resources\PayrollPeriodResource;
use App\Models\PayrollPeriod;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class PayrollPeriodController extends Controller
{
    public function index(IndexPayrollPeriodRequest $request): AnonymousResourceCollection
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

        return PayrollPeriodResource::collection($periods);
    }

    public function store(StorePayrollPeriodRequest $request, CreatePayrollPeriod $action): PayrollPeriodResource
    {
        return PayrollPeriodResource::make($action->handle($request->validated(), $request->user()));
    }

    public function show(PayrollPeriod $payrollPeriod): PayrollPeriodResource
    {
        Gate::authorize('view', $payrollPeriod);

        return PayrollPeriodResource::make($payrollPeriod);
    }

    public function update(UpdatePayrollPeriodRequest $request, PayrollPeriod $payrollPeriod, UpdatePayrollPeriod $action): PayrollPeriodResource
    {
        return PayrollPeriodResource::make($action->handle($payrollPeriod, $request->validated(), $request->user()));
    }

    public function destroy(PayrollPeriod $payrollPeriod): never
    {
        abort(405);
    }
}
