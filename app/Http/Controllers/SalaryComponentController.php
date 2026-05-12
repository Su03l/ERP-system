<?php

namespace App\Http\Controllers;

use App\Actions\CreateSalaryComponent;
use App\Actions\UpdateSalaryComponent;
use App\Http\Requests\IndexSalaryComponentRequest;
use App\Http\Requests\StoreSalaryComponentRequest;
use App\Http\Requests\UpdateSalaryComponentRequest;
use App\Http\Resources\SalaryComponentResource;
use App\Models\SalaryComponent;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class SalaryComponentController extends Controller
{
    public function index(IndexSalaryComponentRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', SalaryComponent::class);
        $filters = $request->validated();

        $components = SalaryComponent::query()
            ->forCurrentCompany()
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('name_ar', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
            }))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate();

        return SalaryComponentResource::collection($components);
    }

    public function store(StoreSalaryComponentRequest $request, CreateSalaryComponent $action): SalaryComponentResource
    {
        return SalaryComponentResource::make($action->handle($request->validated(), $request->user()));
    }

    public function show(SalaryComponent $salaryComponent): SalaryComponentResource
    {
        Gate::authorize('view', $salaryComponent);

        return SalaryComponentResource::make($salaryComponent);
    }

    public function update(UpdateSalaryComponentRequest $request, SalaryComponent $salaryComponent, UpdateSalaryComponent $action): SalaryComponentResource
    {
        return SalaryComponentResource::make($action->handle($salaryComponent, $request->validated(), $request->user()));
    }

    public function destroy(SalaryComponent $salaryComponent): never
    {
        abort(405);
    }
}
