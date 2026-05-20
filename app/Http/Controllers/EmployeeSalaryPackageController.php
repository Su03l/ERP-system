<?php

namespace App\Http\Controllers;

use App\Actions\CreateSalaryPackage;
use App\Actions\UpdateSalaryPackage;
use App\Http\Requests\IndexEmployeeSalaryPackageRequest;
use App\Http\Requests\StoreEmployeeSalaryPackageRequest;
use App\Http\Requests\UpdateEmployeeSalaryPackageRequest;
use App\Http\Resources\EmployeeSalaryPackageResource;
use App\Models\Employee;
use App\Models\EmployeeSalaryPackage;
use Illuminate\Support\Facades\Gate;

class EmployeeSalaryPackageController extends Controller
{
    public function index(IndexEmployeeSalaryPackageRequest $request)
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

        if ($request->expectsJson()) {
            return EmployeeSalaryPackageResource::collection($packages);
        }

        return view('employee-salary-packages.index', compact('packages'));
    }

    public function create()
    {
        Gate::authorize('create', EmployeeSalaryPackage::class);
        $employees = Employee::forCurrentCompany()->get();

        return view('employee-salary-packages.create', compact('employees'));
    }

    public function store(StoreEmployeeSalaryPackageRequest $request, CreateSalaryPackage $action)
    {
        $package = $action->handle($request->validated(), $request->user())->load(['employee', 'items']);

        if ($request->expectsJson()) {
            return EmployeeSalaryPackageResource::make($package);
        }

        return redirect()->route('employee-salary-packages.index')->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء حزمة الراتب بنجاح.' : 'Salary package created.');
    }

    public function show(EmployeeSalaryPackage $employeeSalaryPackage)
    {
        Gate::authorize('view', $employeeSalaryPackage);

        if (request()->expectsJson()) {
            return EmployeeSalaryPackageResource::make($employeeSalaryPackage->load(['employee', 'items']));
        }

        return redirect()->route('employee-salary-packages.edit', $employeeSalaryPackage->id);
    }

    public function edit(EmployeeSalaryPackage $employeeSalaryPackage)
    {
        Gate::authorize('update', $employeeSalaryPackage);
        $employees = Employee::forCurrentCompany()->get();
        $employeeSalaryPackage->load(['employee', 'items']);

        return view('employee-salary-packages.edit', compact('employeeSalaryPackage', 'employees'));
    }

    public function update(UpdateEmployeeSalaryPackageRequest $request, EmployeeSalaryPackage $employeeSalaryPackage, UpdateSalaryPackage $action)
    {
        $result = $action->handle($employeeSalaryPackage, $request->validated(), $request->user())->load(['employee', 'items']);

        if ($request->expectsJson()) {
            return EmployeeSalaryPackageResource::make($result);
        }

        return redirect()->route('employee-salary-packages.index')->with('success', app()->getLocale() === 'ar' ? 'تم تحديث حزمة الراتب بنجاح.' : 'Salary package updated.');
    }

    public function destroy(EmployeeSalaryPackage $employeeSalaryPackage): never
    {
        abort(405);
    }
}
