<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicApiRateLimit
{
    public function __construct(private readonly RateLimiter $limiter) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next, int $maxAttempts = 120, int $decaySeconds = 60): Response
    {
        $key = 'public-api:'.($request->attributes->get('company_id') ?? $request->ip());

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return new JsonResponse(['message' => __('public_api.errors.rate_limited')], 429);
        }

        $this->limiter->hit($key, $decaySeconds);

        return $next($request);
    }
}
