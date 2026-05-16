<?php

namespace App\Http\Controllers;

use App\Actions\RevokeUserSession;
use App\Http\Requests\IndexUserSessionRequest;
use App\Models\UserSession;
use App\Services\UserSessionQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSessionController extends Controller
{
    public function index(IndexUserSessionRequest $request, UserSessionQuery $query): JsonResponse
    {
        return response()->json(['data' => $query->rows($request->user(), $request->validated())]);
    }

    public function destroy(Request $request, UserSession $userSession, RevokeUserSession $action): JsonResponse
    {
        $session = $action->handle($userSession, $request->user());

        return response()->json(['data' => [
            'id' => $session->id,
            'user_id' => $session->user_id,
            'revoked_at' => $session->revoked_at?->toDateTimeString(),
        ]]);
    }
}
