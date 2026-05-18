<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps the backend inside the approved Laravel-native folders', function () {
    $appDirectories = collect(scandir(app_path()))
        ->reject(fn (string $name): bool => str_starts_with($name, '.'))
        ->values()
        ->all();

    expect($appDirectories)->not->toContain('Modules')
        ->and($appDirectories)->not->toContain('Shared')
        ->and($appDirectories)->not->toContain('Domain')
        ->and($appDirectories)->not->toContain('Infrastructure');
});

it('does not use broad throwable catches in backend code', function () {
    $files = collect(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path())))
        ->filter(fn (SplFileInfo $file): bool => $file->isFile() && $file->getExtension() === 'php');

    $matches = $files
        ->filter(fn (SplFileInfo $file): bool => preg_match('/catch\s*\(\s*\\\\?Throwable\b/', file_get_contents($file->getPathname())) === 1)
        ->map(fn (SplFileInfo $file): string => str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname()))
        ->values()
        ->all();

    expect($matches)->toBe([]);
});

it('keeps controllers free of transactions hashing and broad exception handling', function () {
    $controllers = collect(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path('Http/Controllers'))))
        ->filter(fn (SplFileInfo $file): bool => $file->isFile() && $file->getExtension() === 'php');

    foreach ($controllers as $controller) {
        $contents = file_get_contents($controller->getPathname());

        expect($contents)->not->toContain('DB::transaction')
            ->and($contents)->not->toContain('catch (Throwable')
            ->and($contents)->not->toContain('catch (\\Throwable')
            ->and($contents)->not->toContain('hash(');
    }
});
