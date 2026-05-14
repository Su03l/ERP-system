<?php

namespace App\Http\Controllers;

use App\Actions\ActivateCompanyAddOn;
use App\Actions\DeactivateCompanyAddOn;
use App\Actions\UpdateCompanyAddOn;
use App\Http\Requests\IndexCompanyAddOnRequest;
use App\Http\Requests\StoreCompanyAddOnRequest;
use App\Http\Requests\UpdateCompanyAddOnRequest;
use App\Http\Resources\CompanyAddOnResource;
use App\Models\AddOn;
use App\Models\Company;
use App\Models\CompanyAddOn;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CompanyAddOnController extends Controller
{
    public function index(IndexCompanyAddOnRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', CompanyAddOn::class);
        $filters = $request->validated();

        $companyAddOns = CompanyAddOn::query()
            ->with(['company', 'addOn'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['company_id'] ?? null, fn ($query, int $companyId) => $query->where('company_id', $companyId))
            ->when($filters['add_on_id'] ?? null, fn ($query, int $addOnId) => $query->where('add_on_id', $addOnId))
            ->when($filters['starts_from'] ?? null, fn ($query, string $date) => $query->whereDate('starts_at', '>=', $date))
            ->when($filters['starts_until'] ?? null, fn ($query, string $date) => $query->whereDate('starts_at', '<=', $date))
            ->when($filters['ends_from'] ?? null, fn ($query, string $date) => $query->whereDate('ends_at', '>=', $date))
            ->when($filters['ends_until'] ?? null, fn ($query, string $date) => $query->whereDate('ends_at', '<=', $date))
            ->latest('id')
            ->paginate();

        return CompanyAddOnResource::collection($companyAddOns);
    }

    public function store(StoreCompanyAddOnRequest $request, ActivateCompanyAddOn $action): CompanyAddOnResource
    {
        Gate::authorize('create', CompanyAddOn::class);
        $data = $request->validated();
        $company = Company::query()->findOrFail((int) $data['company_id']);
        $addOn = AddOn::query()->findOrFail((int) $data['add_on_id']);

        return CompanyAddOnResource::make($action->handle($company, $addOn, $data, $request->user()));
    }

    public function show(CompanyAddOn $companyAddOn): CompanyAddOnResource
    {
        Gate::authorize('view', $companyAddOn);

        return CompanyAddOnResource::make($companyAddOn->load(['company', 'addOn']));
    }

    public function update(UpdateCompanyAddOnRequest $request, CompanyAddOn $companyAddOn, UpdateCompanyAddOn $action): CompanyAddOnResource
    {
        Gate::authorize('update', $companyAddOn);

        return CompanyAddOnResource::make($action->handle($companyAddOn, $request->validated(), $request->user()));
    }

    public function destroy(CompanyAddOn $companyAddOn): never
    {
        abort(405);
    }

    public function deactivate(Request $request, CompanyAddOn $companyAddOn, DeactivateCompanyAddOn $action): CompanyAddOnResource
    {
        Gate::authorize('update', $companyAddOn);

        return CompanyAddOnResource::make($action->handle($companyAddOn, $request->user(), $request->string('reason')->toString()));
    }
}
