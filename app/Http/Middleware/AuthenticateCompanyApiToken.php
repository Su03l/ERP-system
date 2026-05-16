<?php

namespace App\Http\Middleware;

use App\Services\PublicApiTokenAuthenticator;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateCompanyApiToken
{
    public function __construct(private readonly PublicApiTokenAuthenticator $authenticator) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        $token = $this->authenticator->tokenFromRequest($request);

        if ($token === null || ! $this->authenticator->isValid($token)) {
            return $this->deny(__('public_api.errors.invalid_token'), 401);
        }

        foreach ($abilities as $ability) {
            if (! $token->can($ability)) {
                return $this->deny(__('public_api.errors.missing_ability'), 403);
            }
        }

        $request->attributes->set('company_api_token', $token);
        $request->attributes->set('company', $token->company);
        $request->attributes->set('company_id', $token->company_id);

        $this->authenticator->touch($token);

        return $next($request);
    }

    private function deny(string $message, int $status): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $status);
    }
}
