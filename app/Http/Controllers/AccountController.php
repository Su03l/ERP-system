<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveAccount;
use App\Actions\CreateAccount;
use App\Actions\UpdateAccount;
use App\Http\Requests\IndexAccountRequest;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AccountController extends Controller
{
    public function index(IndexAccountRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Account::class);
        $filters = $request->validated();

        $accounts = Account::query()
            ->forCurrentCompany()
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            }))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when(array_key_exists('parent_id', $filters), fn ($query) => $query->where('parent_id', $filters['parent_id']))
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', $filters['is_active']))
            ->when(array_key_exists('is_system', $filters), fn ($query) => $query->where('is_system', $filters['is_system']))
            ->orderBy('code')
            ->paginate();

        return AccountResource::collection($accounts);
    }

    public function store(StoreAccountRequest $request, CreateAccount $action): AccountResource
    {
        return AccountResource::make($action->handle($request->validated(), $request->user()));
    }

    public function show(Account $account): AccountResource
    {
        Gate::authorize('view', $account);

        return AccountResource::make($account);
    }

    public function update(UpdateAccountRequest $request, Account $account, UpdateAccount $action): AccountResource
    {
        return AccountResource::make($action->handle($account, $request->validated(), $request->user()));
    }

    public function destroy(Account $account, ArchiveAccount $action): Response
    {
        $action->handle($account, request()->user());

        return response()->noContent();
    }
}
