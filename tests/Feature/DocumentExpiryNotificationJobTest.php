<?php

use App\Jobs\ScanDocumentExpiryNotificationsJob;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\DocumentSetting;
use App\Models\User;
use App\Services\DocumentExpiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('is queueable and unique per company per day', function () {
    Carbon::setTestNow('2026-05-13');

    $job = new ScanDocumentExpiryNotificationsJob(5);

    expect($job->uniqueId())->toBe('document-expiry-scan:5:2026-05-13');
});

it('creates database notifications for expiring company documents without duplicates', function () {
    Carbon::setTestNow('2026-05-13');
    config(['queue.default' => 'sync']);
    $company = Company::factory()->create();
    DocumentSetting::factory()->for($company)->create(['default_expiry_reminder_days' => 10]);
    $user = User::factory()->for($company)->create();
    CompanyDocument::factory()->for($company)->create([
        'title_ar' => 'رخصة',
        'expiry_date' => '2026-05-20',
    ]);

    $job = new ScanDocumentExpiryNotificationsJob($company->id);
    $job->handle(app(DocumentExpiryService::class));
    $job->handle(app(DocumentExpiryService::class));

    expect($user->notifications()->count())->toBe(1)
        ->and($user->notifications()->first()->data['state'])->toBe('expiring')
        ->and($user->notifications()->first()->data['title_ar'])->toBe('رخصة');
});
