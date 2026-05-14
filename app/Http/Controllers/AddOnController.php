<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveAddOn;
use App\Actions\CreateAddOn;
use App\Actions\UpdateAddOn;
use App\Http\Requests\IndexAddOnRequest;
use App\Http\Requests\StoreAddOnRequest;
use App\Http\Requests\UpdateAddOnRequest;
use App\Http\Resources\AddOnResource;
use App\Models\AddOn;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AddOnController extends Controller
{
    public function index(IndexAddOnRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', AddOn::class);
        $filters = $request->validated();

        $addOns = AddOn::query()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->where('category', $category))
            ->when($filters['feature_key'] ?? null, fn ($query, string $featureKey) => $query->where('feature_key', $featureKey))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            }))
            ->latest('id')
            ->paginate();

        return AddOnResource::collection($addOns);
    }

    public function store(StoreAddOnRequest $request, CreateAddOn $action): AddOnResource
    {
        Gate::authorize('create', AddOn::class);

        return AddOnResource::make($action->handle($request->validated(), $request->user()));
    }

    public function show(AddOn $addOn): AddOnResource
    {
        Gate::authorize('view', $addOn);

        return AddOnResource::make($addOn);
    }

    public function update(UpdateAddOnRequest $request, AddOn $addOn, UpdateAddOn $action): AddOnResource
    {
        Gate::authorize('update', $addOn);

        return AddOnResource::make($action->handle($addOn, $request->validated(), $request->user()));
    }

    public function destroy(AddOn $addOn, ArchiveAddOn $action): Response
    {
        Gate::authorize('delete', $addOn);
        $action->handle($addOn, request()->user());

        return response()->noContent();
    }
}
