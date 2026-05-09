<?php

use App\Enums\JobTitleStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\JobTitle;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantJobTitlePermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();
    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey, 'description' => null]);
        $role->permissions()->attach($permission);
    }
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('job title routes manage tenant scoped records', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantJobTitlePermissions($user, ['job_titles.view', 'job_titles.create', 'job_titles.update', 'job_titles.delete']);
    $jobTitle = JobTitle::factory()->for($company)->create(['name_ar' => 'محاسب', 'status' => JobTitleStatus::Active->value]);
    JobTitle::factory()->for($otherCompany)->create(['name_ar' => 'محاسب']);

    $this->actingAs($user)
        ->getJson('/job-titles?search=محاسب&status=active')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $jobTitle->id);

    $this->postJson('/job-titles', [
        'name_ar' => 'مدير مالي',
        'name_en' => 'Finance Manager',
        'code' => 'FIN-MGR',
        'status' => JobTitleStatus::Active->value,
    ])->assertSuccessful()->assertJsonPath('data.company_id', $company->id);

    $this->patchJson("/job-titles/{$jobTitle->id}", ['name_ar' => 'محاسب أول'])
        ->assertSuccessful()
        ->assertJsonPath('data.name_ar', 'محاسب أول');

    $this->deleteJson("/job-titles/{$jobTitle->id}")->assertNoContent();

    expect(AuditLog::query()->where('action', 'job_title.created')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'job_title.updated')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'job_title.archived')->exists())->toBeTrue();
});

test('job title validation is tenant aware', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantJobTitlePermissions($user, ['job_titles.create']);
    JobTitle::factory()->for($company)->create(['code' => 'DUP']);
    JobTitle::factory()->for($otherCompany)->create(['code' => 'OTHER']);

    $this->actingAs($user)
        ->postJson('/job-titles', ['name_ar' => 'مكرر', 'code' => 'DUP', 'status' => JobTitleStatus::Active->value])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code']);

    $this->postJson('/job-titles', ['name_ar' => 'مسموح', 'code' => 'OTHER', 'status' => JobTitleStatus::Active->value])
        ->assertSuccessful();
});
