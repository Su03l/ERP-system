<?php

namespace App\Http\Controllers;

use App\Actions\CreateAssetCategory;
use App\Actions\UpdateAssetCategory;
use App\Http\Requests\IndexAssetCategoryRequest;
use App\Http\Requests\StoreAssetCategoryRequest;
use App\Http\Requests\UpdateAssetCategoryRequest;
use App\Http\Resources\AssetCategoryResource;
use App\Models\AssetCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AssetCategoryController extends Controller
{
    public function index(IndexAssetCategoryRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', AssetCategory::class);
        $filters = $request->validated();

        $assetCategories = AssetCategory::query()
            ->forCurrentCompany()
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            }))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when(array_key_exists('parent_id', $filters), fn ($query) => $query->where('parent_id', $filters['parent_id']))
            ->orderBy('name_ar')
            ->paginate();

        return AssetCategoryResource::collection($assetCategories);
    }

    public function store(StoreAssetCategoryRequest $request, CreateAssetCategory $action): AssetCategoryResource
    {
        return AssetCategoryResource::make($action->handle($request->validated(), $request->user()));
    }

    public function show(AssetCategory $assetCategory): AssetCategoryResource
    {
        Gate::authorize('view', $assetCategory);

        return AssetCategoryResource::make($assetCategory);
    }

    public function update(UpdateAssetCategoryRequest $request, AssetCategory $assetCategory, UpdateAssetCategory $action): AssetCategoryResource
    {
        return AssetCategoryResource::make($action->handle($assetCategory, $request->validated(), $request->user()));
    }

    public function destroy(AssetCategory $assetCategory): Response
    {
        Gate::authorize('delete', $assetCategory);
        $assetCategory->delete();

        return response()->noContent();
    }
}
