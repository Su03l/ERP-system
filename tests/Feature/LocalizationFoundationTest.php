<?php

use App\Models\Company;
use App\Models\User;
use App\Services\LocaleResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('unsupported locales fallback to arabic', function () {
    $resolver = app(LocaleResolver::class);

    expect($resolver->sanitize('fr'))->toBe('ar')
        ->and($resolver->sanitize(null))->toBe('ar')
        ->and($resolver->sanitize('en'))->toBe('en')
        ->and($resolver->sanitize('ar'))->toBe('ar');
});

test('locale direction is resolved from sanitized locale', function () {
    $resolver = app(LocaleResolver::class);

    expect($resolver->direction('ar'))->toBe('rtl')
        ->and($resolver->direction('en'))->toBe('ltr')
        ->and($resolver->direction('fr'))->toBe('rtl');
});

test('user preferred locale wins over company locale', function () {
    $company = Company::factory()->create(['locale' => 'ar']);
    $user = User::factory()->for($company)->create(['preferred_locale' => 'en']);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $locale = app(LocaleResolver::class)->resolveForRequest($request);

    expect($locale)->toBe('en')
        ->and($user->preferredLocale())->toBe('en');
});

test('invalid user preferred locale falls back to arabic', function () {
    $company = Company::factory()->create(['locale' => 'en']);
    $user = User::factory()->for($company)->create(['preferred_locale' => 'fr']);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    expect(app(LocaleResolver::class)->resolveForRequest($request))->toBe('ar');
});

test('company locale is used when user has no preferred locale', function () {
    $company = Company::factory()->create(['locale' => 'en']);
    $user = User::factory()->for($company)->create(['preferred_locale' => null]);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    expect(app(LocaleResolver::class)->resolveForRequest($request))->toBe('en')
        ->and($user->preferredLocale())->toBe('en');
});

test('session locale is used when no user or company locale exists', function () {
    Route::middleware('web')->get('/locale-session-probe', fn () => App::currentLocale());

    $this->withSession(['locale' => 'en'])
        ->get('/locale-session-probe')
        ->assertSuccessful()
        ->assertSee('en');
});

test('request locale is sanitized before applying locale', function () {
    Route::middleware('web')->get('/locale-request-probe', fn () => App::currentLocale());

    $this->get('/locale-request-probe?locale=fr')
        ->assertSuccessful()
        ->assertSee('ar');
});

test('middleware applies resolved locale for request', function () {
    Route::middleware('web')->get('/locale-probe', fn () => App::currentLocale());

    $company = Company::factory()->create(['locale' => 'ar']);
    $user = User::factory()->for($company)->create(['preferred_locale' => 'en']);

    $this->actingAs($user)
        ->get('/locale-probe')
        ->assertSuccessful()
        ->assertSee('en');
});

test('middleware exposes locale and text direction on the request', function () {
    Route::middleware('web')->get('/locale-direction-probe', function (Request $request) {
        return response()->json([
            'locale' => $request->attributes->get('locale'),
            'text_direction' => $request->attributes->get('text_direction'),
        ]);
    });

    $this->get('/locale-direction-probe?locale=ar')
        ->assertSuccessful()
        ->assertJson([
            'locale' => 'ar',
            'text_direction' => 'rtl',
        ]);
});
