<?php

namespace App\Http\Middleware;

use App\Services\LocaleResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ApplyLocale
{
    public function __construct(private readonly LocaleResolver $localeResolver) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->localeResolver->resolveForRequest($request);

        App::setLocale($locale);
        $request->attributes->set('locale', $locale);
        $request->attributes->set('text_direction', $this->localeResolver->direction($locale));

        return $next($request);
    }
}
