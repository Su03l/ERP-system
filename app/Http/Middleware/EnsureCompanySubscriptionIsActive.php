<?php

namespace App\Http\Middleware;

use App\Services\CompanySubscriptionAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySubscriptionIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessService = app(CompanySubscriptionAccessService::class);

        if (! $accessService->canAccess($request->user())) {
            $message = $accessService->denialMessage($request->user()?->company?->subscriptions()->latest('id')->first());

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 402);
            }

            abort(402, $message);
        }

        return $next($request);
    }
}
