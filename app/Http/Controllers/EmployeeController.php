<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveEmployee;
use App\Actions\CreateEmployee;
use App\Actions\UpdateEmployee;
use App\Http\Requests\IndexEmployeeRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeIndexQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    public function index(IndexEmployeeRequest $request, EmployeeIndexQuery $employeeIndexQuery): AnonymousResourceCollection
    {
        return EmployeeResource::collection($employeeIndexQuery->paginate($request->validated()));
    }

    public function store(StoreEmployeeRequest $request, CreateEmployee $createEmployee): EmployeeResource
    {
        $employee = $createEmployee->handle($request->validated(), $request->user());

        return EmployeeResource::make($employee->load(['department', 'jobTitle', 'manager']));
    }

    public function show(Employee $employee): EmployeeResource
    {
        Gate::authorize('view', $employee);

        return EmployeeResource::make($employee->load(['department', 'jobTitle', 'manager']));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, UpdateEmployee $updateEmployee): EmployeeResource
    {
        $employee = $updateEmployee->handle($employee, $request->validated(), $request->user());

        return EmployeeResource::make($employee->load(['department', 'jobTitle', 'manager']));
    }

    public function destroy(Employee $employee, ArchiveEmployee $archiveEmployee): JsonResponse
    {
        $archiveEmployee->handle($employee, request()->user());

        return response()->json(status: 204);
    }
}
