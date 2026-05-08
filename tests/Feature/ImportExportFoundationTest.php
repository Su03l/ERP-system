<?php

use App\Models\Company;
use App\Models\ExportJob;
use App\Models\ImportJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('import jobs track tenant scoped row progress and errors', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $importJob = ImportJob::factory()->for($company)->for($user)->create([
        'status' => 'failed',
        'file_path' => 'imports/employees.csv',
        'entity_type' => 'employees',
        'module_key' => 'hr',
        'error_summary' => [
            'rows' => [
                ['row' => 7, 'message' => 'Email is required'],
            ],
        ],
        'processed_rows' => 10,
        'failed_rows' => 1,
        'total_rows' => 11,
    ]);

    expect($importJob->company->is($company))->toBeTrue()
        ->and($importJob->user->is($user))->toBeTrue()
        ->and($importJob->error_summary['rows'][0]['row'])->toBe(7)
        ->and(ImportJob::forCompany($company)->first()->is($importJob))->toBeTrue();
});

test('export jobs track tenant scoped file generation progress', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $exportJob = ExportJob::factory()->for($company)->for($user)->create([
        'status' => 'completed',
        'file_path' => 'exports/assets.xlsx',
        'entity_type' => 'assets',
        'module_key' => 'assets',
        'processed_rows' => 25,
        'failed_rows' => 0,
        'total_rows' => 25,
        'finished_at' => now(),
    ]);

    expect($exportJob->company->is($company))->toBeTrue()
        ->and($exportJob->user->is($user))->toBeTrue()
        ->and($exportJob->file_path)->toBe('exports/assets.xlsx')
        ->and(ExportJob::forCompany($company)->first()->is($exportJob))->toBeTrue();
});
