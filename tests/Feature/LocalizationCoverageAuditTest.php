<?php

use App\Services\LocaleResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

it('keeps Arabic as the default and fallback backend locale', function () {
    expect(config('app.locale'))->toBe('ar')
        ->and(config('app.fallback_locale'))->toBe('ar');
});

it('falls unsupported locales back to Arabic direction and locale', function () {
    $resolver = app(LocaleResolver::class);
    $request = request();
    $request->headers->set('Accept-Language', 'fr');

    expect($resolver->resolveForRequest($request))->toBe('ar')
        ->and($resolver->direction('ar'))->toBe('rtl')
        ->and($resolver->direction('en'))->toBe('ltr');
});

it('has Arabic and English labels for security audit and export keys', function (string $key) {
    App::setLocale('ar');
    expect(__($key))->not->toBe($key);

    App::setLocale('en');
    expect(__($key))->not->toBe($key);
})->with([
    'security.audit.actions.api_token_created',
    'security.audit.actions.api_token_revoked',
    'security.audit.actions.security_settings_updated',
    'security.audit.actions.sensitive_export_requested',
    'security.audit.actions.sensitive_export_approval_required',
    'security.audit.actions.webhook_endpoint_created',
    'security.audit.actions.webhook_endpoint_updated',
    'security.audit.actions.webhook_endpoint_deleted',
    'security.exports.columns.name',
    'security.exports.columns.event',
    'security.exports.columns.company_id',
]);
