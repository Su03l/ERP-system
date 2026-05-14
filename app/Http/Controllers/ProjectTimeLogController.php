<?php

namespace App\Http\Controllers;

use App\Actions\CreateManualTimeLog;
use App\Actions\DeleteTimeLog;
use App\Actions\UpdateTimeLog;
use App\Http\Requests\IndexProjectTimeLogRequest;
use App\Http\Requests\StoreProjectTimeLogRequest;
use App\Http\Requests\UpdateProjectTimeLogRequest;
use App\Http\Resources\ProjectTimeLogResource;
use App\Models\ProjectTimeLog;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProjectTimeLogController extends Controller
{
    public function index(IndexProjectTimeLogRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', ProjectTimeLog::class);
        $filters = $request->validated();

        $timeLogs = ProjectTimeLog::query()
            ->with(['project', 'projectTask'])
            ->forCurrentCompany()
            ->when($filters['project_id'] ?? null, fn ($query, int $projectId) => $query->where('project_id', $projectId))
            ->when($filters['project_task_id'] ?? null, fn ($query, int $taskId) => $query->where('project_task_id', $taskId))
            ->when($filters['employee_id'] ?? null, fn ($query, int $employeeId) => $query->where('employee_id', $employeeId))
            ->when(array_key_exists('is_billable', $filters), fn ($query) => $query->where('is_billable', $filters['is_billable']))
            ->when($filters['logged_from'] ?? null, fn ($query, string $date) => $query->whereDate('log_date', '>=', $date))
            ->when($filters['logged_until'] ?? null, fn ($query, string $date) => $query->whereDate('log_date', '<=', $date))
            ->latest('id')
            ->paginate();

        return ProjectTimeLogResource::collection($timeLogs);
    }

    public function store(StoreProjectTimeLogRequest $request, CreateManualTimeLog $action): ProjectTimeLogResource
    {
        return ProjectTimeLogResource::make($action->handle($request->validated(), $request->user())->load(['project', 'projectTask']));
    }

    public function show(ProjectTimeLog $projectTimeLog): ProjectTimeLogResource
    {
        Gate::authorize('view', $projectTimeLog);

        return ProjectTimeLogResource::make($projectTimeLog->load(['project', 'projectTask']));
    }

    public function update(UpdateProjectTimeLogRequest $request, ProjectTimeLog $projectTimeLog, UpdateTimeLog $action): ProjectTimeLogResource
    {
        return ProjectTimeLogResource::make($action->handle($projectTimeLog, $request->validated(), $request->user())->load(['project', 'projectTask']));
    }

    public function destroy(ProjectTimeLog $projectTimeLog, DeleteTimeLog $action): Response
    {
        $action->handle($projectTimeLog, request()->user());

        return response()->noContent();
    }
}
