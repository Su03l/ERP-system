<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveAsset;
use App\Actions\CreateAsset;
use App\Actions\UpdateAsset;
use App\Http\Requests\IndexAssetRequest;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AssetController extends Controller
{
    public function index(IndexAssetRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Asset::class);
        $filters = $request->validated();
        $categoryId = $filters['asset_category_id'] ?? $filters['category'] ?? null;
        $assignedEmployeeId = $filters['assigned_employee_id'] ?? $filters['assigned_employee'] ?? null;

        $assets = Asset::query()
            ->with(['category'])
            ->forCurrentCompany()
            ->when($categoryId, fn ($query, int $assetCategoryId) => $query->where('asset_category_id', $assetCategoryId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($assignedEmployeeId, fn ($query, int $employeeId) => $query->where('assigned_employee_id', $employeeId))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('asset_code', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            }))
            ->when($filters['purchased_from'] ?? null, fn ($query, string $date) => $query->whereDate('purchase_date', '>=', $date))
            ->when($filters['purchased_until'] ?? null, fn ($query, string $date) => $query->whereDate('purchase_date', '<=', $date))
            ->latest('id')
            ->paginate();

        return AssetResource::collection($assets);
    }

    public function store(StoreAssetRequest $request, CreateAsset $action): AssetResource
    {
        return AssetResource::make($action->handle($request->validated(), $request->user())->load('category'));
    }

    public function show(Asset $asset): AssetResource
    {
        Gate::authorize('view', $asset);

        return AssetResource::make($asset->load('category'));
    }

    public function update(UpdateAssetRequest $request, Asset $asset, UpdateAsset $action): AssetResource
    {
        return AssetResource::make($action->handle($asset, $request->validated(), $request->user())->load('category'));
    }

    public function destroy(Asset $asset, ArchiveAsset $action): Response
    {
        $action->handle($asset, request()->user());

        return response()->noContent();
    }
}
