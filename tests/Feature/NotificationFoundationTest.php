<?php

use App\Models\Company;
use App\Models\User;
use App\Services\NotificationPreferenceResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('user model is prepared for database notifications', function () {
    $user = User::factory()->create();

    $notification = DatabaseNotification::query()->create([
        'id' => (string) Str::uuid(),
        'type' => 'workflow.approval.requested',
        'notifiable_type' => $user->getMorphClass(),
        'notifiable_id' => $user->id,
        'data' => ['message' => 'Approval requested'],
    ]);

    expect($user->notifications()->first()->is($notification))->toBeTrue();
});

test('notification channels follow company preferences', function () {
    $company = Company::factory()->create([
        'settings' => [
            'notification_preferences' => [
                'database_enabled' => true,
                'email_enabled' => false,
                'sms_enabled' => true,
                'whatsapp_enabled' => true,
            ],
        ],
    ]);
    $user = User::factory()->for($company)->create();

    $resolver = app(NotificationPreferenceResolver::class);

    expect($resolver->channelsFor($user, 'workflow.approval.requested'))->toBe(['database'])
        ->and($resolver->futureChannelsFor($user))->toBe(['sms', 'whatsapp']);
});
