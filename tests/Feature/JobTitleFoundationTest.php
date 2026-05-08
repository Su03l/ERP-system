<?php

use App\Models\Company;
use App\Models\JobTitle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('job title belongs to a company and supports localized names', function () {
    $company = Company::factory()->create();
    $jobTitle = JobTitle::factory()->for($company)->create([
        'name_ar' => 'مدير الموارد البشرية',
        'name_en' => 'HR Manager',
    ]);

    expect($jobTitle->company->is($company))->toBeTrue()
        ->and($jobTitle->name_ar)->toBe('مدير الموارد البشرية')
        ->and($jobTitle->name_en)->toBe('HR Manager');
});

test('company has many job titles', function () {
    $company = Company::factory()->create();
    $jobTitle = JobTitle::factory()->for($company)->create();

    expect($company->jobTitles()->first()->is($jobTitle))->toBeTrue();
});

test('job titles can be scoped by company', function () {
    $firstCompany = Company::factory()->create();
    $secondCompany = Company::factory()->create();
    $firstJobTitle = JobTitle::factory()->for($firstCompany)->create();

    JobTitle::factory()->for($secondCompany)->create();

    expect(JobTitle::forCompany($firstCompany)->pluck('id')->all())->toBe([$firstJobTitle->id]);
});
