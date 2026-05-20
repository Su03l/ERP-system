<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveDepartment;
use App\Actions\CreateDepartment;
use App\Actions\UpdateDepartment;
use App\Http\Requests\IndexDepartmentRequest;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\Employee;
use App\Services\DepartmentIndexQuery;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
{
    public function index(IndexDepartmentRequest $request, DepartmentIndexQuery $departmentIndexQuery)
    {
        $departments = $departmentIndexQuery->paginate($request->validated());

        if ($request->expectsJson()) {
            return DepartmentResource::collection($departments);
        }

        return view('departments.index', compact('departments'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        Gate::authorize('create', Department::class);

        $departments = Department::forCurrentCompany()->get();
        $managers = Employee::forCompany($user->company)->get();

        return view('departments.create', compact('departments', 'managers'));
    }

    public function store(StoreDepartmentRequest $request, CreateDepartment $createDepartment)
    {
        $department = $createDepartment->handle($request->validated(), $request->user());

        if ($request->expectsJson()) {
            return DepartmentResource::make($department->load('parent'));
        }

        return redirect()->route('departments.index')->with('success', __('hr.department_created_successfully'));
    }

    public function show(Department $department)
    {
        Gate::authorize('view', $department);

        if (request()->expectsJson()) {
            return DepartmentResource::make($department->load('parent'));
        }

        return view('departments.show', compact('department'));
    }

    public function edit(Request $request, Department $department)
    {
        Gate::authorize('update', $department);

        $user = $request->user();
        $departments = Department::forCurrentCompany()->where('id', '!=', $department->id)->get();
        $managers = Employee::forCompany($user->company)->get();

        return view('departments.edit', compact('department', 'departments', 'managers'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department, UpdateDepartment $updateDepartment)
    {
        $department = $updateDepartment->handle($department, $request->validated(), $request->user());

        if ($request->expectsJson()) {
            return DepartmentResource::make($department->load('parent'));
        }

        return redirect()->route('departments.index')->with('success', __('hr.department_updated_successfully'));
    }

    public function destroy(Department $department, ArchiveDepartment $archiveDepartment)
    {
        $archiveDepartment->handle($department, request()->user());

        if (request()->expectsJson()) {
            return response()->json(status: 204);
        }

        return redirect()->route('departments.index')->with('success', __('hr.department_deleted_successfully'));
    }
}
