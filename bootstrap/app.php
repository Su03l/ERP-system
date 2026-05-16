<?php

use App\Http\Middleware\ApplyLocale;
use App\Http\Middleware\AuthenticateCompanyApiToken;
use App\Http\Middleware\EnsureCompanySubscriptionIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            ApplyLocale::class,
        ]);
        $middleware->alias([
            'company.api' => AuthenticateCompanyApiToken::class,
            'subscription.active' => EnsureCompanySubscriptionIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
