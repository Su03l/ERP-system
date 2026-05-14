<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveCrmLead;
use App\Actions\ConvertLeadToCustomer;
use App\Actions\CreateCrmLead;
use App\Actions\UpdateCrmLead;
use App\Http\Requests\IndexCrmLeadRequest;
use App\Http\Requests\StoreCrmLeadRequest;
use App\Http\Requests\UpdateCrmLeadRequest;
use App\Http\Resources\CrmLeadResource;
use App\Models\CrmLead;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CrmLeadController extends Controller
{
    public function index(IndexCrmLeadRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', CrmLead::class);
        $filters = $request->validated();
        $assignedUserId = $filters['assigned_user_id'] ?? $filters['assigned_user'] ?? null;

        $leads = CrmLead::query()
            ->with('assignedUser')
            ->forCurrentCompany()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($assignedUserId, fn ($query, int $userId) => $query->where('assigned_user_id', $userId))
            ->when($filters['source'] ?? null, fn ($query, string $source) => $query->where('source', $source))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->when($filters['created_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['created_until'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate();

        return CrmLeadResource::collection($leads);
    }

    public function store(StoreCrmLeadRequest $request, CreateCrmLead $action): CrmLeadResource
    {
        return CrmLeadResource::make($action->handle($request->validated(), $request->user())->load('assignedUser'));
    }

    public function show(CrmLead $crmLead): CrmLeadResource
    {
        Gate::authorize('view', $crmLead);

        return CrmLeadResource::make($crmLead->load('assignedUser'));
    }

    public function update(UpdateCrmLeadRequest $request, CrmLead $crmLead, UpdateCrmLead $action): CrmLeadResource
    {
        return CrmLeadResource::make($action->handle($crmLead, $request->validated(), $request->user())->load('assignedUser'));
    }

    public function destroy(CrmLead $crmLead, ArchiveCrmLead $action): Response
    {
        $action->handle($crmLead, request()->user());

        return response()->noContent();
    }

    public function convert(CrmLead $crmLead, ConvertLeadToCustomer $action): Response
    {
        Gate::authorize('convert', $crmLead);
        $action->handle($crmLead, request()->user());

        return response()->noContent();
    }
}
