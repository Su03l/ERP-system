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

test('user preferred locale wins over company locale', function () {
    $company = Company::factory()->create(['locale' => 'ar']);
    $user = User::factory()->for($company)->create(['preferred_locale' => 'en']);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $locale = app(LocaleResolver::class)->resolveForRequest($request);

    expect($locale)->toBe('en')
        ->and($user->preferredLocale())->toBe('en');
});

test('company locale is used when user has no preferred locale', function () {
    $company = Company::factory()->create(['locale' => 'en']);
    $user = User::factory()->for($company)->create(['preferred_locale' => null]);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    expect(app(LocaleResolver::class)->resolveForRequest($request))->toBe('en')
        ->and($user->preferredLocale())->toBe('en');
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
