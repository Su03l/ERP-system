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
use App\Services\DepartmentIndexQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
{
    public function index(IndexDepartmentRequest $request, DepartmentIndexQuery $departmentIndexQuery): AnonymousResourceCollection
    {
        return DepartmentResource::collection($departmentIndexQuery->paginate($request->validated()));
    }

    public function store(StoreDepartmentRequest $request, CreateDepartment $createDepartment): DepartmentResource
    {
        $department = $createDepartment->handle($request->validated(), $request->user());

        return DepartmentResource::make($department->load('parent'));
    }

    public function show(Department $department): DepartmentResource
    {
        Gate::authorize('view', $department);

        return DepartmentResource::make($department->load('parent'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department, UpdateDepartment $updateDepartment): DepartmentResource
    {
        $department = $updateDepartment->handle($department, $request->validated(), $request->user());

        return DepartmentResource::make($department->load('parent'));
    }

    public function destroy(Department $department, ArchiveDepartment $archiveDepartment): JsonResponse
    {
        $archiveDepartment->handle($department, request()->user());

        return response()->json(status: 204);
    }
}
