<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('department belongs to a company and supports localized names', function () {
    $company = Company::factory()->create();
    $department = Department::factory()->for($company)->create([
        'name_ar' => 'الموارد البشرية',
        'name_en' => 'Human Resources',
    ]);

    expect($department->company->is($company))->toBeTrue()
        ->and($department->name_ar)->toBe('الموارد البشرية')
        ->and($department->name_en)->toBe('Human Resources');
});

test('department supports hierarchy and manager relationships', function () {
    $company = Company::factory()->create();
    $manager = User::factory()->for($company)->create();
    $parent = Department::factory()->for($company)->create();
    $child = Department::factory()->for($company)->create([
        'parent_id' => $parent->id,
        'manager_id' => $manager->id,
    ]);

    expect($child->parent->is($parent))->toBeTrue()
        ->and($parent->children()->first()->is($child))->toBeTrue()
        ->and($child->manager->is($manager))->toBeTrue()
        ->and($manager->managedDepartments()->first()->is($child))->toBeTrue();
});

test('departments can be scoped by company', function () {
    $firstCompany = Company::factory()->create();
    $secondCompany = Company::factory()->create();
    $firstDepartment = Department::factory()->for($firstCompany)->create();

    Department::factory()->for($secondCompany)->create();

    expect(Department::forCompany($firstCompany)->pluck('id')->all())->toBe([$firstDepartment->id]);
});
