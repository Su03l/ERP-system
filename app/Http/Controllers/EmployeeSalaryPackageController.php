<?php

namespace App\Http\Controllers;

use App\Actions\CreateSalaryPackage;
use App\Actions\UpdateSalaryPackage;
use App\Http\Requests\IndexEmployeeSalaryPackageRequest;
use App\Http\Requests\StoreEmployeeSalaryPackageRequest;
use App\Http\Requests\UpdateEmployeeSalaryPackageRequest;
use App\Http\Resources\EmployeeSalaryPackageResource;
use App\Models\EmployeeSalaryPackage;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class EmployeeSalaryPackageController extends Controller
{
    public function index(IndexEmployeeSalaryPackageRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', EmployeeSalaryPackage::class);
        $filters = $request->validated();

        $packages = EmployeeSalaryPackage::query()
            ->forCurrentCompany()
            ->with(['employee', 'items'])
            ->when($filters['employee_id'] ?? null, fn ($query, int $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate();

        return EmployeeSalaryPackageResource::collection($packages);
    }

    public function store(StoreEmployeeSalaryPackageRequest $request, CreateSalaryPackage $action): EmployeeSalaryPackageResource
    {
        return EmployeeSalaryPackageResource::make($action->handle($request->validated(), $request->user())->load(['employee', 'items']));
    }

    public function show(EmployeeSalaryPackage $employeeSalaryPackage): EmployeeSalaryPackageResource
    {
        Gate::authorize('view', $employeeSalaryPackage);

        return EmployeeSalaryPackageResource::make($employeeSalaryPackage->load(['employee', 'items']));
    }

    public function update(UpdateEmployeeSalaryPackageRequest $request, EmployeeSalaryPackage $employeeSalaryPackage, UpdateSalaryPackage $action): EmployeeSalaryPackageResource
    {
        return EmployeeSalaryPackageResource::make($action->handle($employeeSalaryPackage, $request->validated(), $request->user())->load(['employee', 'items']));
    }

    public function destroy(EmployeeSalaryPackage $employeeSalaryPackage): never
    {
        abort(405);
    }
}
