<?php

use App\Enums\ContactStatus;
use App\Enums\LeadStatus;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectTaskStatus;
use App\Models\Company;
use App\Models\CrmContact;
use App\Models\CrmLead;
use App\Models\ProjectCrmSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('provides stable CRM and project enum values', function () {
    expect(LeadStatus::values())->toBe(['new', 'contacted', 'qualified', 'converted', 'lost'])
        ->and(ContactStatus::values())->toBe(['active', 'inactive', 'archived'])
        ->and(ProjectStatus::values())->toBe(['draft', 'pending_approval', 'active', 'on_hold', 'completed', 'cancelled'])
        ->and(ProjectTaskStatus::values())->toBe(['todo', 'pending_approval', 'in_progress', 'review', 'completed', 'cancelled'])
        ->and(ProjectPriority::values())->toBe(['low', 'medium', 'high', 'urgent']);
});

it('provides localized CRM and project enum labels', function () {
    app()->setLocale('en');

    expect(LeadStatus::New->label())->toBe('New')
        ->and(ContactStatus::Archived->label())->toBe('Archived')
        ->and(ProjectStatus::OnHold->label())->toBe('On hold')
        ->and(ProjectTaskStatus::InProgress->label())->toBe('In progress')
        ->and(ProjectPriority::Urgent->label())->toBe('Urgent');
});

it('casts CRM model statuses to enums', function () {
    $company = Company::factory()->create();

    $lead = CrmLead::factory()->for($company)->create(['status' => LeadStatus::Qualified]);
    $contact = CrmContact::factory()->for($company)->create(['status' => ContactStatus::Inactive]);
    $setting = ProjectCrmSetting::factory()->for($company)->create(['default_project_status' => ProjectStatus::Active]);

    expect($lead->status)->toBe(LeadStatus::Qualified)
        ->and($contact->status)->toBe(ContactStatus::Inactive)
        ->and($setting->default_project_status)->toBe(ProjectStatus::Active);
});
