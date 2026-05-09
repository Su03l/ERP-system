<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveJobTitle;
use App\Actions\CreateJobTitle;
use App\Actions\UpdateJobTitle;
use App\Http\Requests\IndexJobTitleRequest;
use App\Http\Requests\StoreJobTitleRequest;
use App\Http\Requests\UpdateJobTitleRequest;
use App\Http\Resources\JobTitleResource;
use App\Models\JobTitle;
use App\Services\JobTitleIndexQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class JobTitleController extends Controller
{
    public function index(IndexJobTitleRequest $request, JobTitleIndexQuery $query): AnonymousResourceCollection
    {
        return JobTitleResource::collection($query->paginate($request->validated()));
    }

    public function store(StoreJobTitleRequest $request, CreateJobTitle $action): JobTitleResource
    {
        return JobTitleResource::make($action->handle($request->validated(), $request->user()));
    }

    public function show(JobTitle $jobTitle): JobTitleResource
    {
        Gate::authorize('view', $jobTitle);

        return JobTitleResource::make($jobTitle);
    }

    public function update(UpdateJobTitleRequest $request, JobTitle $jobTitle, UpdateJobTitle $action): JobTitleResource
    {
        return JobTitleResource::make($action->handle($jobTitle, $request->validated(), $request->user()));
    }

    public function destroy(JobTitle $jobTitle, ArchiveJobTitle $action): JsonResponse
    {
        $action->handle($jobTitle, request()->user());

        return response()->json(status: 204);
    }
}
