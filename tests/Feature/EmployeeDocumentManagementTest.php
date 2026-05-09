<?php

use App\Actions\CreateEmployeeDocument;
use App\Actions\DeleteEmployeeDocument;
use App\Actions\UpdateEmployeeDocument;
use App\Http\Requests\StoreEmployeeDocumentRequest;
use App\Http\Requests\UpdateEmployeeDocumentRequest;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function grantEmployeeDocumentPermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            [
                'name' => $permissionKey,
                'description' => null,
            ],
        );

        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function employeeDocumentPayload(array $overrides = []): array
{
    return array_merge([
        'document_type' => 'contract',
        'title_ar' => 'عقد العمل',
        'title_en' => 'Employment Contract',
        'issue_date' => '2026-01-01',
        'expiry_date' => '2026-12-31',
        'status' => 'active',
        'metadata' => ['source' => 'manual'],
    ], $overrides);
}

test('store employee document request accepts only employees inside current company', function () {
    Route::post('/employee-document-validation-store', fn (StoreEmployeeDocumentRequest $request) => $request->validated());

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    grantEmployeeDocumentPermissions($user, ['employee_documents.create']);

    $this->actingAs($user)
        ->postJson('/employee-document-validation-store', employeeDocumentPayload(['employee_id' => $otherEmployee->id]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['employee_id']);

    $this->actingAs($user)
        ->postJson('/employee-document-validation-store', employeeDocumentPayload(['employee_id' => $employee->id]))
        ->assertSuccessful()
        ->assertJsonPath('employee_id', $employee->id);
});

test('update employee document request blocks moving document to employee from another company', function () {
    Route::patch('/employee-document-validation-update/{employee_document}', fn (UpdateEmployeeDocumentRequest $request, EmployeeDocument $employeeDocument) => $request->validated());

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    $document = EmployeeDocument::factory()->for($company)->for($employee)->create();
    grantEmployeeDocumentPermissions($user, ['employee_documents.update']);

    $this->actingAs($user)
        ->patchJson("/employee-document-validation-update/{$document->id}", ['employee_id' => $otherEmployee->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['employee_id']);
});

test('employee document actions create update and delete tenant scoped documents with audit logs', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantEmployeeDocumentPermissions($user, [
        'employee_documents.create',
        'employee_documents.update',
        'employee_documents.delete',
    ]);

    $this->actingAs($user);

    $document = app(CreateEmployeeDocument::class)->handle(
        employeeDocumentPayload(['employee_id' => $employee->id]),
        $user,
    );

    $updatedDocument = app(UpdateEmployeeDocument::class)->handle($document, ['title_ar' => 'عقد عمل محدث'], $user);

    app(DeleteEmployeeDocument::class)->handle($updatedDocument, $user);

    expect($document->company_id)->toBe($company->id)
        ->and($updatedDocument->title_ar)->toBe('عقد عمل محدث')
        ->and(EmployeeDocument::query()->whereKey($document->id)->exists())->toBeFalse()
        ->and(AuditLog::query()->where('action', 'employee_document.created')->where('auditable_id', $document->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'employee_document.updated')->where('auditable_id', $document->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'employee_document.deleted')->where('auditable_id', $document->id)->exists())->toBeTrue();
});

test('employee document actions reject cross company records', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($otherCompany)->create();
    $document = EmployeeDocument::factory()->for($otherCompany)->for($employee)->create();
    grantEmployeeDocumentPermissions($user, ['employee_documents.update']);

    $this->actingAs($user);

    app(UpdateEmployeeDocument::class)->handle($document, ['title_ar' => 'محاولة'], $user);
})->throws(AuthorizationException::class);

test('employee document policy protects permissions and company boundary', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    $document = EmployeeDocument::factory()->for($company)->for($employee)->create();
    $otherDocument = EmployeeDocument::factory()->for($otherCompany)->for($otherEmployee)->create();

    expect($user->can('view', $document))->toBeFalse();

    grantEmployeeDocumentPermissions($user, ['employee_documents.view']);

    expect($user->can('view', $document))->toBeTrue()
        ->and($user->can('view', $otherDocument))->toBeFalse();
});
