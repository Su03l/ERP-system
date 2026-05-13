<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveCrmContact;
use App\Actions\CreateCrmContact;
use App\Actions\UpdateCrmContact;
use App\Http\Requests\IndexCrmContactRequest;
use App\Http\Requests\StoreCrmContactRequest;
use App\Http\Requests\UpdateCrmContactRequest;
use App\Http\Resources\CrmContactResource;
use App\Models\CrmContact;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CrmContactController extends Controller
{
    public function index(IndexCrmContactRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', CrmContact::class);
        $filters = $request->validated();

        $contacts = CrmContact::query()
            ->with(['customer', 'lead'])
            ->forCurrentCompany()
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['customer_id'] ?? null, fn ($query, int $customerId) => $query->where('customer_id', $customerId))
            ->when($filters['lead_id'] ?? null, fn ($query, int $leadId) => $query->where('lead_id', $leadId))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            }))
            ->when($filters['created_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['created_until'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate();

        return CrmContactResource::collection($contacts);
    }

    public function store(StoreCrmContactRequest $request, CreateCrmContact $action): CrmContactResource
    {
        return CrmContactResource::make($action->handle($request->validated(), $request->user())->load(['customer', 'lead']));
    }

    public function show(CrmContact $crmContact): CrmContactResource
    {
        Gate::authorize('view', $crmContact);

        return CrmContactResource::make($crmContact->load(['customer', 'lead']));
    }

    public function update(UpdateCrmContactRequest $request, CrmContact $crmContact, UpdateCrmContact $action): CrmContactResource
    {
        return CrmContactResource::make($action->handle($crmContact, $request->validated(), $request->user())->load(['customer', 'lead']));
    }

    public function destroy(CrmContact $crmContact, ArchiveCrmContact $action): Response
    {
        $action->handle($crmContact, request()->user());

        return response()->noContent();
    }
}
