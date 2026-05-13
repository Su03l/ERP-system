<?php

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\DocumentExportQuery;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function grantDocumentExportPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('exports company and employee documents with filters and hides file paths by default', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantDocumentExportPermissions($actor, ['company_documents.view', 'employee_documents.view']);
    $employee = Employee::factory()->for($company)->create();
    CompanyDocument::factory()->for($company)->create([
        'document_type' => DocumentType::Contract,
        'status' => DocumentStatus::Active,
        'title_ar' => 'عقد',
        'expiry_date' => '2026-05-20',
        'file_path' => 'secret.pdf',
    ]);
    EmployeeDocument::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'document_type' => DocumentType::Passport,
        'status' => DocumentStatus::Active,
        'expiry_date' => '2026-05-20',
    ]);
    CompanyDocument::factory()->for(Company::factory())->create(['expiry_date' => '2026-05-20']);

    $service = app(DocumentExportQuery::class);

    $companyRows = $service->companyDocuments(['document_type' => DocumentType::Contract->value, 'include_file_paths' => true], $actor);
    $employeeRows = $service->employeeDocuments(['document_type' => DocumentType::Passport->value], $actor);

    expect($companyRows)->toHaveCount(1)
        ->and($companyRows[0]['title_ar'])->toBe('عقد')
        ->and($companyRows[0]['file_path'])->toBeNull()
        ->and($employeeRows)->toHaveCount(1)
        ->and($employeeRows[0]['document_type'])->toBe('passport');
});

it('exports expiring and expired document rows', function () {
    Carbon::setTestNow('2026-05-13');
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantDocumentExportPermissions($actor, ['company_documents.view', 'employee_documents.view']);
    CompanyDocument::factory()->for($company)->create(['expiry_date' => '2026-05-20']);
    CompanyDocument::factory()->for($company)->create(['expiry_date' => '2026-05-01']);

    $service = app(DocumentExportQuery::class);

    expect($service->expiring([], $actor, 10))->toHaveCount(1)
        ->and($service->expired([], $actor))->toHaveCount(1);
});

it('requires document view permissions', function () {
    $actor = User::factory()->for(Company::factory())->create();

    app(DocumentExportQuery::class)->companyDocuments([], $actor);
})->throws(AuthorizationException::class);
