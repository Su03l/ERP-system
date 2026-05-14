<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveProjectTask;
use App\Actions\CompleteProjectTask;
use App\Actions\CreateProjectTask;
use App\Actions\UpdateProjectTask;
use App\Http\Requests\IndexProjectTaskRequest;
use App\Http\Requests\StoreProjectTaskRequest;
use App\Http\Requests\UpdateProjectTaskRequest;
use App\Http\Resources\ProjectTaskResource;
use App\Models\ProjectTask;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProjectTaskController extends Controller
{
    public function index(IndexProjectTaskRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', ProjectTask::class);
        $filters = $request->validated();

        $tasks = ProjectTask::query()
            ->with('project')
            ->forCurrentCompany()
            ->when($filters['project_id'] ?? null, fn ($query, int $projectId) => $query->where('project_id', $projectId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($query, string $priority) => $query->where('priority', $priority))
            ->when($filters['assigned_employee_id'] ?? null, fn ($query, int $employeeId) => $query->where('assigned_employee_id', $employeeId))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('task_code', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%");
            }))
            ->when($filters['due_from'] ?? null, fn ($query, string $date) => $query->whereDate('due_date', '>=', $date))
            ->when($filters['due_until'] ?? null, fn ($query, string $date) => $query->whereDate('due_date', '<=', $date))
            ->when(isset($filters['progress_min']), fn ($query) => $query->where('progress_percentage', '>=', (int) $filters['progress_min']))
            ->when(isset($filters['progress_max']), fn ($query) => $query->where('progress_percentage', '<=', (int) $filters['progress_max']))
            ->latest('id')
            ->paginate();

        return ProjectTaskResource::collection($tasks);
    }

    public function store(StoreProjectTaskRequest $request, CreateProjectTask $action): ProjectTaskResource
    {
        return ProjectTaskResource::make($action->handle($request->validated(), $request->user())->load('project'));
    }

    public function show(ProjectTask $projectTask): ProjectTaskResource
    {
        Gate::authorize('view', $projectTask);

        return ProjectTaskResource::make($projectTask->load('project'));
    }

    public function update(UpdateProjectTaskRequest $request, ProjectTask $projectTask, UpdateProjectTask $action): ProjectTaskResource
    {
        return ProjectTaskResource::make($action->handle($projectTask, $request->validated(), $request->user())->load('project'));
    }

    public function complete(ProjectTask $projectTask, CompleteProjectTask $action): ProjectTaskResource
    {
        Gate::authorize('complete', $projectTask);

        return ProjectTaskResource::make($action->handle($projectTask, request()->user())->load('project'));
    }

    public function destroy(ProjectTask $projectTask, ArchiveProjectTask $action): Response
    {
        $action->handle($projectTask, request()->user());

        return response()->noContent();
    }
}
