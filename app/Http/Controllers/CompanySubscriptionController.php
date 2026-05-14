<?php

namespace App\Http\Controllers;

use App\Actions\CancelSubscription;
use App\Actions\CreateCompanySubscription;
use App\Actions\UpdateCompanySubscription;
use App\Http\Requests\IndexCompanySubscriptionRequest;
use App\Http\Requests\StoreCompanySubscriptionRequest;
use App\Http\Requests\UpdateCompanySubscriptionRequest;
use App\Http\Resources\CompanySubscriptionResource;
use App\Models\CompanySubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CompanySubscriptionController extends Controller
{
    public function index(IndexCompanySubscriptionRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', CompanySubscription::class);
        $filters = $request->validated();

        $subscriptions = CompanySubscription::query()
            ->with(['company', 'plan'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['plan_id'] ?? null, fn ($query, int $planId) => $query->where('plan_id', $planId))
            ->when($filters['company_id'] ?? null, fn ($query, int $companyId) => $query->where('company_id', $companyId))
            ->when($filters['billing_cycle'] ?? null, fn ($query, string $cycle) => $query->where('billing_cycle', $cycle))
            ->when($filters['starts_from'] ?? null, fn ($query, string $date) => $query->whereDate('starts_at', '>=', $date))
            ->when($filters['starts_until'] ?? null, fn ($query, string $date) => $query->whereDate('starts_at', '<=', $date))
            ->when($filters['ends_from'] ?? null, fn ($query, string $date) => $query->whereDate('ends_at', '>=', $date))
            ->when($filters['ends_until'] ?? null, fn ($query, string $date) => $query->whereDate('ends_at', '<=', $date))
            ->latest('id')
            ->paginate();

        return CompanySubscriptionResource::collection($subscriptions);
    }

    public function store(StoreCompanySubscriptionRequest $request, CreateCompanySubscription $action): CompanySubscriptionResource
    {
        Gate::authorize('create', CompanySubscription::class);

        return CompanySubscriptionResource::make($action->handle($request->validated(), $request->user()));
    }

    public function show(CompanySubscription $companySubscription): CompanySubscriptionResource
    {
        Gate::authorize('view', $companySubscription);

        return CompanySubscriptionResource::make($companySubscription->load(['company', 'plan']));
    }

    public function update(UpdateCompanySubscriptionRequest $request, CompanySubscription $companySubscription, UpdateCompanySubscription $action): CompanySubscriptionResource
    {
        Gate::authorize('update', $companySubscription);

        return CompanySubscriptionResource::make($action->handle($companySubscription, $request->validated(), $request->user()));
    }

    public function destroy(CompanySubscription $companySubscription): never
    {
        abort(405);
    }

    public function cancel(Request $request, CompanySubscription $companySubscription, CancelSubscription $action): CompanySubscriptionResource
    {
        Gate::authorize('cancel', $companySubscription);

        return CompanySubscriptionResource::make($action->handle($companySubscription, $request->user()));
    }
}
