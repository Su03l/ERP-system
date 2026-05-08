<?php

use App\Models\Company;
use App\Models\ImportJob;
use App\Models\MigrationSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('migration sessions track wizard mapping validation and import progress', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $importJob = ImportJob::factory()->for($company)->for($user)->create([
        'entity_type' => 'employees',
        'module_key' => 'hr',
    ]);

    $session = MigrationSession::factory()->for($company)->for($user)->for($importJob)->create([
        'uploaded_file_path' => 'migration-uploads/employees.csv',
        'target_entity' => 'employees',
        'module_key' => 'hr',
        'column_mapping' => [
            'Employee Name' => 'name',
            'Work Email' => 'email',
        ],
        'validation_result' => [
            'valid_rows' => 20,
            'invalid_rows' => 2,
        ],
        'dry_run_status' => 'completed',
        'final_import_status' => 'pending',
    ]);

    expect($session->company->is($company))->toBeTrue()
        ->and($session->user->is($user))->toBeTrue()
        ->and($session->importJob->is($importJob))->toBeTrue()
        ->and($session->column_mapping['Work Email'])->toBe('email')
        ->and($session->validation_result['invalid_rows'])->toBe(2)
        ->and(MigrationSession::forCompany($company)->first()->is($session))->toBeTrue();
});
