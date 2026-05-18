<?php

use App\Models\CompanyApiToken;
use App\Models\WebhookEndpoint;
use App\Services\LocaleResolver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

test('backend keeps approved Laravel native structure without forbidden architecture folders', function () {
    expect(is_dir(app_path('Modules')))->toBeFalse()
        ->and(is_dir(app_path('Shared')))->toBeFalse()
        ->and(is_dir(base_path('Domain')))->toBeFalse()
        ->and(is_dir(base_path('Infrastructure')))->toBeFalse()
        ->and(is_dir(app_path('Actions')))->toBeTrue()
        ->and(is_dir(app_path('Services')))->toBeTrue()
        ->and(is_dir(app_path('Support')))->toBeTrue();
});

test('backend code does not use broad catch throwable blocks', function () {
    $files = collect(iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path()))))
        ->filter(fn (SplFileInfo $file): bool => $file->isFile() && $file->getExtension() === 'php')
        ->map(fn (SplFileInfo $file): string => $file->getPathname());

    $matches = $files
        ->filter(fn (string $file): bool => Str::contains(file_get_contents($file) ?: '', 'catch (Throwable'))
        ->values();

    expect($matches)->toBeEmpty();
});

test('core backend routes remain registered for ui and integration agents', function () {
    expect(Route::has('analytics.kpis.index'))->toBeTrue()
        ->and(Route::has('analytics.reports.export'))->toBeTrue()
        ->and(Route::has('public-api.company.show'))->toBeTrue()
        ->and(Route::has('payroll-run-items.payslip'))->toBeTrue();
});

test('arabic remains the safe localization fallback', function () {
    $resolver = app(LocaleResolver::class);

    expect($resolver->sanitize('ar'))->toBe('ar')
        ->and($resolver->sanitize('en'))->toBe('en')
        ->and($resolver->sanitize('unsupported'))->toBe('ar')
        ->and($resolver->direction('unsupported'))->toBe('rtl');
});

test('sensitive token and webhook secret columns are hidden from serialized output', function () {
    $apiToken = new CompanyApiToken([
        'name' => 'Integration',
        'token' => hash('sha256', 'not-plain'),
    ]);
    $webhook = new WebhookEndpoint([
        'name' => 'Endpoint',
        'secret_hash' => hash('sha256', 'not-plain'),
    ]);

    expect($apiToken->toArray())->not->toHaveKey('token')
        ->and($webhook->toArray())->not->toHaveKey('secret_hash');
});
