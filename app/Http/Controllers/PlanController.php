<?php

namespace App\Http\Controllers;

use App\Actions\ArchivePlan;
use App\Actions\CreatePlan;
use App\Actions\UpdatePlan;
use App\Http\Requests\IndexPlanRequest;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PlanController extends Controller
{
    public function index(IndexPlanRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Plan::class);
        $filters = $request->validated();

        $plans = Plan::query()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            }))
            ->latest('id')
            ->paginate();

        return PlanResource::collection($plans);
    }

    public function store(StorePlanRequest $request, CreatePlan $action): PlanResource
    {
        Gate::authorize('create', Plan::class);

        return PlanResource::make($action->handle($request->validated(), $request->user()));
    }

    public function show(Plan $plan): PlanResource
    {
        Gate::authorize('view', $plan);

        return PlanResource::make($plan);
    }

    public function update(UpdatePlanRequest $request, Plan $plan, UpdatePlan $action): PlanResource
    {
        Gate::authorize('update', $plan);

        return PlanResource::make($action->handle($plan, $request->validated(), $request->user()));
    }

    public function destroy(Plan $plan, ArchivePlan $action): Response
    {
        Gate::authorize('delete', $plan);
        $action->handle($plan, request()->user());

        return response()->noContent();
    }
}
