<?php

use App\Actions\ArchiveCompanyDocument;
use App\Actions\CreateCompanyDocument;
use App\Actions\UpdateCompanyDocument;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Http\Requests\StoreCompanyDocumentRequest;
use App\Http\Requests\UpdateCompanyDocumentRequest;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::post('/test/company-documents', fn (StoreCompanyDocumentRequest $request) => $request->validated());
    Route::patch('/test/company-documents/{companyDocument}', fn (UpdateCompanyDocumentRequest $request, CompanyDocument $companyDocument) => $request->validated());
});

function grantCompanyDocumentPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('validates company document payloads with enums', function () {
    $actor = User::factory()->for(Company::factory())->create();
    grantCompanyDocumentPermissions($actor, ['company_documents.create']);

    $this->actingAs($actor)
        ->postJson('/test/company-documents', [
            'document_type' => 'bad',
            'status' => 'bad',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['document_type', 'title_ar', 'status']);

    $this->actingAs($actor)
        ->postJson('/test/company-documents', [
            'document_type' => DocumentType::Contract->value,
            'title_ar' => 'عقد',
            'status' => DocumentStatus::Active->value,
        ])
        ->assertSuccessful();
});

it('creates updates and archives company documents with audit logs', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantCompanyDocumentPermissions($actor, ['company_documents.create', 'company_documents.update', 'company_documents.delete']);
    $this->actingAs($actor);

    $document = app(CreateCompanyDocument::class)->handle([
        'document_type' => DocumentType::Contract,
        'title_ar' => 'عقد شركة',
        'status' => DocumentStatus::Active,
    ], $actor);

    app(UpdateCompanyDocument::class)->handle($document, ['title_ar' => 'عقد محدث'], $actor);
    app(ArchiveCompanyDocument::class)->handle($document, $actor);

    expect($document->refresh()->company_id)->toBe($company->id)
        ->and($document->title_ar)->toBe('عقد محدث')
        ->and($document->status)->toBe(DocumentStatus::Archived)
        ->and(AuditLog::query()->where('action', 'company_document.created')->where('auditable_id', $document->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'company_document.updated')->where('auditable_id', $document->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'company_document.archived')->where('auditable_id', $document->id)->exists())->toBeTrue();
});

it('protects company document policy permissions and company boundary', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantCompanyDocumentPermissions($actor, ['company_documents.view', 'company_documents.create', 'company_documents.update', 'company_documents.delete']);
    $document = CompanyDocument::factory()->for($company)->create();
    $otherDocument = CompanyDocument::factory()->for(Company::factory())->create();

    expect(Gate::forUser($actor)->allows('viewAny', CompanyDocument::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('create', CompanyDocument::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('view', $document))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('update', $document))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('delete', $document))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('view', $otherDocument))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('delete', $otherDocument))->toBeTrue();
});
