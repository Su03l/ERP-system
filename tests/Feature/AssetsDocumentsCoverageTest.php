<?php

use App\Actions\AssignAssetToEmployee;
use App\Actions\CreateCompanyDocument;
use App\Actions\ReturnAssetFromEmployee;
use App\Enums\AssetDepreciationMethod;
use App\Enums\AssetStatus;
use App\Enums\CustodyStatus;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Jobs\ScanDocumentExpiryNotificationsJob;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetSetting;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\DocumentSetting;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AssetDepreciationService;
use App\Services\DocumentExpiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function grantAssetDocumentCoveragePermissions215(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            ['name' => $permissionKey, 'description' => null],
        );

        $role->permissions()->syncWithoutDetaching([$permission->id]);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('asset categories and assets enforce tenant scoped relations', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $category = AssetCategory::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    grantAssetDocumentCoveragePermissions215($user, ['asset_categories.view', 'assets.create']);

    $this->actingAs($user)
        ->getJson('/asset-categories')
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $category->id);

    $this->actingAs($user)
        ->postJson('/assets', [
            'asset_category_id' => $category->id,
            'assigned_employee_id' => $otherEmployee->id,
            'asset_code' => 'AST-215',
            'name_ar' => 'Laptop',
            'status' => AssetStatus::Available->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['assigned_employee_id']);
});

test('asset custody assignment and return update the asset safely', function () {
    $company = Company::factory()->create();
    AssetSetting::factory()->for($company)->create([
        'custody_approval_required' => false,
        'asset_return_approval_required' => false,
    ]);
    $actor = User::factory()->for($company)->create();
    $asset = Asset::factory()->for($company)->create(['status' => AssetStatus::Available]);
    $employee = Employee::factory()->for($company)->create();
    grantAssetDocumentCoveragePermissions215($actor, ['asset_custody.create', 'asset_custody.return']);

    $this->actingAs($actor);

    $custody = app(AssignAssetToEmployee::class)->handle($asset, $employee, $actor);

    expect($custody->status)->toBe(CustodyStatus::Assigned)
        ->and($asset->refresh()->assigned_employee_id)->toBe($employee->id)
        ->and($asset->status)->toBe(AssetStatus::Assigned);

    $returned = app(ReturnAssetFromEmployee::class)->handle($asset->refresh(), $actor);

    expect($returned->status)->toBe(CustodyStatus::Returned)
        ->and($asset->refresh()->assigned_employee_id)->toBeNull()
        ->and($asset->status)->toBe(AssetStatus::Available);
});

test('straight line depreciation uses exact expected values', function () {
    $asset = Asset::factory()->create([
        'purchase_date' => '2026-01-15',
        'purchase_cost' => '12000.00',
        'salvage_value' => '0.00',
        'useful_life_months' => 12,
        'depreciation_method' => AssetDepreciationMethod::StraightLine,
    ]);

    expect(app(AssetDepreciationService::class)->calculate($asset, Carbon::parse('2026-03-31')))->toBe([
        'depreciation_amount' => '1000.00',
        'accumulated_depreciation' => '3000.00',
        'book_value' => '9000.00',
    ]);
});

test('company documents and expiry notifications are tenant safe and duplicate safe', function () {
    Carbon::setTestNow('2026-05-13');
    config(['queue.default' => 'sync']);

    $company = Company::factory()->create();
    DocumentSetting::factory()->for($company)->create(['default_expiry_reminder_days' => 10]);
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantAssetDocumentCoveragePermissions215($user, ['company_documents.create']);

    $this->actingAs($user);

    $companyDocument = app(CreateCompanyDocument::class)->handle([
        'document_type' => DocumentType::Contract,
        'title_ar' => 'License',
        'expiry_date' => '2026-05-20',
        'status' => DocumentStatus::Active,
    ], $user);
    EmployeeDocument::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'expiry_date' => '2026-05-12',
    ]);
    CompanyDocument::factory()->for(Company::factory())->create(['expiry_date' => '2026-05-20']);

    $expiry = app(DocumentExpiryService::class);

    expect($companyDocument->company_id)->toBe($company->id)
        ->and($expiry->expiredForCurrentCompany()['employee_documents'])->toHaveCount(1)
        ->and($expiry->expiringWithinForCurrentCompany(10)['company_documents'])->toHaveCount(1);

    $job = new ScanDocumentExpiryNotificationsJob($company->id);
    $job->handle($expiry);
    $job->handle($expiry);

    expect($user->notifications()->count())->toBe(2);

    Carbon::setTestNow();
});
