<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveProject;
use App\Actions\CreateProject;
use App\Actions\UpdateProject;
use App\Http\Requests\IndexProjectRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(IndexProjectRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Project::class);
        $filters = $request->validated();

        $projects = Project::query()
            ->with(['customer', 'projectManager'])
            ->forCurrentCompany()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($query, string $priority) => $query->where('priority', $priority))
            ->when($filters['customer_id'] ?? null, fn ($query, int $customerId) => $query->where('customer_id', $customerId))
            ->when($filters['project_manager_id'] ?? null, fn ($query, int $employeeId) => $query->where('project_manager_id', $employeeId))
            ->when($filters['assigned_employee_id'] ?? null, fn ($query, int $employeeId) => $query->whereHas('tasks', fn ($query) => $query->where('assigned_employee_id', $employeeId)))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            }))
            ->when($filters['starts_from'] ?? null, fn ($query, string $date) => $query->whereDate('start_date', '>=', $date))
            ->when($filters['starts_until'] ?? null, fn ($query, string $date) => $query->whereDate('start_date', '<=', $date))
            ->when($filters['ends_from'] ?? null, fn ($query, string $date) => $query->whereDate('end_date', '>=', $date))
            ->when($filters['ends_until'] ?? null, fn ($query, string $date) => $query->whereDate('end_date', '<=', $date))
            ->when(isset($filters['progress_min']), fn ($query) => $query->where('progress_percentage', '>=', (int) $filters['progress_min']))
            ->when(isset($filters['progress_max']), fn ($query) => $query->where('progress_percentage', '<=', (int) $filters['progress_max']))
            ->latest('id')
            ->paginate();

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request, CreateProject $action): ProjectResource
    {
        return ProjectResource::make($action->handle($request->validated(), $request->user())->load(['customer', 'projectManager']));
    }

    public function show(Project $project): ProjectResource
    {
        Gate::authorize('view', $project);

        return ProjectResource::make($project->load(['customer', 'projectManager']));
    }

    public function update(UpdateProjectRequest $request, Project $project, UpdateProject $action): ProjectResource
    {
        return ProjectResource::make($action->handle($project, $request->validated(), $request->user())->load(['customer', 'projectManager']));
    }

    public function destroy(Project $project, ArchiveProject $action): Response
    {
        $action->handle($project, request()->user());

        return response()->noContent();
    }
}
