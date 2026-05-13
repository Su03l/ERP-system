<?php

use App\Actions\AssignAssetToEmployee;
use App\Actions\ReturnAssetFromEmployee;
use App\Enums\AssetStatus;
use App\Enums\CustodyStatus;
use App\Models\Asset;
use App\Models\AssetCustody;
use App\Models\AssetSetting;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantAssetCustodyPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('assigns available assets to employees without workflow when approval is disabled', function () {
    $company = Company::factory()->create();
    AssetSetting::factory()->for($company)->create(['custody_approval_required' => false]);
    $actor = User::factory()->for($company)->create();
    grantAssetCustodyPermissions($actor, ['asset_custody.create']);
    $asset = Asset::factory()->for($company)->create(['status' => AssetStatus::Available]);
    $employee = Employee::factory()->for($company)->create();
    $this->actingAs($actor);

    $custody = app(AssignAssetToEmployee::class)->handle($asset, $employee, notesAr: 'تسليم');

    expect($custody->status)->toBe(CustodyStatus::Assigned)
        ->and($custody->assigned_by)->toBe($actor->id)
        ->and($asset->refresh()->assigned_employee_id)->toBe($employee->id)
        ->and($asset->status)->toBe(AssetStatus::Assigned)
        ->and(AuditLog::query()->where('action', 'asset_custody.assigned')->where('auditable_id', $custody->id)->exists())->toBeTrue();
});

it('does not assign unavailable assets', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAssetCustodyPermissions($actor, ['asset_custody.create']);
    $asset = Asset::factory()->for($company)->create(['status' => AssetStatus::Retired]);
    $employee = Employee::factory()->for($company)->create();
    $this->actingAs($actor);

    app(AssignAssetToEmployee::class)->handle($asset, $employee);
})->throws(ValidationException::class);

it('returns assigned assets without workflow when approval is disabled', function () {
    $company = Company::factory()->create();
    AssetSetting::factory()->for($company)->create(['asset_return_approval_required' => false]);
    $actor = User::factory()->for($company)->create();
    grantAssetCustodyPermissions($actor, ['asset_custody.return']);
    $employee = Employee::factory()->for($company)->create();
    $asset = Asset::factory()->for($company)->create([
        'assigned_employee_id' => $employee->id,
        'status' => AssetStatus::Assigned,
    ]);
    $custody = AssetCustody::factory()->for($company)->create([
        'asset_id' => $asset->id,
        'employee_id' => $employee->id,
        'status' => CustodyStatus::Assigned,
        'assigned_at' => now()->subDay(),
    ]);
    $this->actingAs($actor);

    $returned = app(ReturnAssetFromEmployee::class)->handle($asset, notesAr: 'استرجاع');

    expect($returned->is($custody))->toBeTrue()
        ->and($returned->status)->toBe(CustodyStatus::Returned)
        ->and($returned->return_received_by)->toBe($actor->id)
        ->and($asset->refresh()->assigned_employee_id)->toBeNull()
        ->and($asset->status)->toBe(AssetStatus::Available)
        ->and(AuditLog::query()->where('action', 'asset_custody.returned')->where('auditable_id', $custody->id)->exists())->toBeTrue();
});

it('requires custody permissions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $asset = Asset::factory()->for($company)->create(['status' => AssetStatus::Available]);
    $employee = Employee::factory()->for($company)->create();
    $this->actingAs($actor);

    app(AssignAssetToEmployee::class)->handle($asset, $employee);
})->throws(AuthorizationException::class);
