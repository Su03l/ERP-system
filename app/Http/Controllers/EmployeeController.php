<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveEmployee;
use App\Actions\CreateEmployee;
use App\Actions\UpdateEmployee;
use App\Http\Requests\IndexEmployeeRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Services\EmployeeIndexQuery;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    public function index(IndexEmployeeRequest $request, EmployeeIndexQuery $employeeIndexQuery)
    {
        $employees = $employeeIndexQuery->paginate($request->validated());

        if ($request->expectsJson()) {
            return EmployeeResource::collection($employees);
        }

        $departments = Department::forCurrentCompany()->get();
        $jobTitles = JobTitle::forCurrentCompany()->get();

        return view('employees.index', [
            'employees' => $employees,
            'departments' => $departments,
            'jobTitles' => $jobTitles,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        Gate::authorize('create', Employee::class);

        $departments = Department::forCurrentCompany()->get();
        $jobTitles = JobTitle::forCurrentCompany()->get();
        $managers = Employee::forCompany($user->company)->get();

        return view('employees.create', compact('departments', 'jobTitles', 'managers'));
    }

    public function store(StoreEmployeeRequest $request, CreateEmployee $createEmployee)
    {
        $employee = $createEmployee->handle($request->validated(), $request->user());

        if ($request->expectsJson()) {
            return EmployeeResource::make($employee->load(['department', 'jobTitle', 'manager']));
        }

        return redirect()->route('employees.index')->with('success', __('hr.employee_created_successfully'));
    }

    public function show(Employee $employee)
    {
        Gate::authorize('view', $employee);

        if (request()->expectsJson()) {
            return EmployeeResource::make($employee->load(['department', 'jobTitle', 'manager']));
        }

        return view('employees.show', compact('employee'));
    }

    public function edit(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $user = $request->user();
        $departments = Department::forCurrentCompany()->get();
        $jobTitles = JobTitle::forCurrentCompany()->get();
        $managers = Employee::forCompany($user->company)->where('id', '!=', $employee->id)->get();

        return view('employees.edit', compact('employee', 'departments', 'jobTitles', 'managers'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, UpdateEmployee $updateEmployee)
    {
        $employee = $updateEmployee->handle($employee, $request->validated(), $request->user());

        if ($request->expectsJson()) {
            return EmployeeResource::make($employee->load(['department', 'jobTitle', 'manager']));
        }

        return redirect()->route('employees.index')->with('success', __('hr.employee_updated_successfully'));
    }

    public function destroy(Employee $employee, ArchiveEmployee $archiveEmployee)
    {
        $archiveEmployee->handle($employee, request()->user());

        if (request()->expectsJson()) {
            return response()->json(status: 204);
        }

        return redirect()->route('employees.index')->with('success', __('hr.employee_deleted_successfully'));
    }
}
