<?php

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the projects schema', function () {
    expect(Schema::hasColumns('projects', [
        'id',
        'company_id',
        'customer_id',
        'project_manager_id',
        'code',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'start_date',
        'end_date',
        'budget',
        'status',
        'priority',
        'progress_percentage',
        'metadata',
        'created_at',
        'updated_at',
        'deleted_at',
    ]))->toBeTrue();
});

it('stores tenant scoped projects with customer and manager relationships', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->for($company)->create();
    $manager = Employee::factory()->for($company)->create();

    $project = Project::factory()->for($company)->create([
        'customer_id' => $customer->id,
        'project_manager_id' => $manager->id,
        'code' => 'PRJ-001',
        'name_ar' => 'مشروع تجريبي',
        'budget' => '25000.50',
        'status' => ProjectStatus::Active,
        'priority' => ProjectPriority::High,
        'metadata' => ['phase' => 'foundation'],
    ]);

    expect($project->company->is($company))->toBeTrue()
        ->and($company->projects()->whereKey($project)->exists())->toBeTrue()
        ->and($project->customer->is($customer))->toBeTrue()
        ->and($project->projectManager->is($manager))->toBeTrue()
        ->and($customer->projects()->whereKey($project)->exists())->toBeTrue()
        ->and($manager->managedProjects()->whereKey($project)->exists())->toBeTrue()
        ->and($project->budget)->toBe('25000.50')
        ->and($project->status)->toBe(ProjectStatus::Active)
        ->and($project->priority)->toBe(ProjectPriority::High)
        ->and($project->metadata)->toBe(['phase' => 'foundation']);
});

it('scopes projects to the current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    Project::factory()->for($otherCompany)->create();

    $this->actingAs($user);

    expect(Project::query()->forCurrentCompany()->pluck('id')->all())->toBe([$project->id]);
});

it('supports soft deleting projects', function () {
    $project = Project::factory()->create();

    $project->delete();

    expect(Project::query()->whereKey($project)->exists())->toBeFalse()
        ->and(Project::withTrashed()->whereKey($project)->exists())->toBeTrue();
});
