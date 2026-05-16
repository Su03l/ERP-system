<?php

namespace App\Http\Controllers;

use App\Actions\CreateApiToken;
use App\Actions\RevokeApiToken;
use App\Http\Requests\StoreApiTokenRequest;
use App\Models\CompanyApiToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApiTokenController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', CompanyApiToken::class);

        $tokens = CompanyApiToken::query()
            ->forCompany($request->user()->company_id)
            ->latest('id')
            ->get()
            ->map(fn (CompanyApiToken $token): array => $this->tokenData($token))
            ->all();

        return response()->json(['data' => $tokens]);
    }

    public function store(StoreApiTokenRequest $request, CreateApiToken $action): JsonResponse
    {
        $result = $action->handle($request->validated(), $request->user());

        return response()->json([
            'data' => [
                ...$this->tokenData($result['token']),
                'plain_text_token' => $result['plain_text_token'],
            ],
        ], 201);
    }

    public function destroy(CompanyApiToken $companyApiToken, RevokeApiToken $action, Request $request): JsonResponse
    {
        $token = $action->handle($companyApiToken, $request->user());

        return response()->json(['data' => $this->tokenData($token)]);
    }

    /** @return array<string, mixed> */
    private function tokenData(CompanyApiToken $token): array
    {
        return [
            'id' => $token->id,
            'company_id' => $token->company_id,
            'user_id' => $token->user_id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at?->toDateTimeString(),
            'expires_at' => $token->expires_at?->toDateTimeString(),
            'revoked_at' => $token->revoked_at?->toDateTimeString(),
            'metadata' => $token->metadata,
        ];
    }
}
