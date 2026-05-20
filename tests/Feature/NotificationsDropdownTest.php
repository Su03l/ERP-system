<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::create([
        'name' => 'شركة نوات البرمجية',
        'subdomain' => 'nawwat',
        'status' => 'active',
        'settings' => [],
    ]);

    $this->user = User::create([
        'name' => 'أحمد علي',
        'email' => 'ahmed@nawwat.com',
        'password' => bcrypt('password123'),
        'company_id' => $this->company->id,
    ]);

    $this->otherUser = User::create([
        'name' => 'خالد محمد',
        'email' => 'khaled@nawwat.com',
        'password' => bcrypt('password123'),
        'company_id' => $this->company->id,
    ]);
});

test('unauthenticated users are redirected from notification mark-as-read routes', function () {
    $uuid = Str::uuid()->toString();

    $this->post(route('notifications.mark-as-read', $uuid))
        ->assertRedirect(route('login'));

    $this->post(route('notifications.mark-all-as-read'))
        ->assertRedirect(route('login'));
});

test('it renders empty state notifications correctly for an authenticated user', function () {
    app()->setLocale('ar');
    $this->actingAs($this->user);

    $view = $this->blade('<x-notifications-dropdown />');

    $view->assertSee('التنبيهات الإدارية');
    $view->assertSee('لا توجد تنبيهات جديدة');
    $view->assertSee('ستظهر هنا آخر الإشعارات المتعلقة بحسابك أو نظام الأمان.');
    $view->assertDontSee('bg-rose-600'); // No unread counter badge
});

test('it displays correct unread counter and messages for database notifications', function () {
    app()->setLocale('en');
    $this->actingAs($this->user);

    // Seed a security notification
    DB::table('notifications')->insert([
        'id' => Str::uuid()->toString(),
        'type' => 'App\Notifications\SecurityEventNotification',
        'notifiable_type' => 'App\Models\User',
        'notifiable_id' => $this->user->id,
        'data' => json_encode([
            'type' => 'security_event',
            'event' => 'api_token_created',
            'message' => 'New API Token has been created successfully',
        ]),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Seed a subscription notification
    DB::table('notifications')->insert([
        'id' => Str::uuid()->toString(),
        'type' => 'App\Notifications\SubscriptionExpiryNotification',
        'notifiable_type' => 'App\Models\User',
        'notifiable_id' => $this->user->id,
        'data' => json_encode([
            'type' => 'subscription_expiry',
            'message' => 'Your Nawwat subscription expires in 3 days',
        ]),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $view = $this->blade('<x-notifications-dropdown />');

    // Unread count is 2
    $view->assertSee('2 new');
    $view->assertSee('New API Token has been created successfully');
    $view->assertSee('Your Nawwat subscription expires in 3 days');
});

test('authorized users can mark their own notification as read', function () {
    $this->actingAs($this->user);

    $notificationId = Str::uuid()->toString();

    DB::table('notifications')->insert([
        'id' => $notificationId,
        'type' => 'App\Notifications\SecurityEventNotification',
        'notifiable_type' => 'App\Models\User',
        'notifiable_id' => $this->user->id,
        'data' => json_encode([
            'type' => 'security_event',
            'message' => 'Password changed successfully',
        ]),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Verify it is unread first
    $this->assertEquals(1, $this->user->unreadNotifications()->count());

    // Submit mark as read request
    $response = $this->post(route('notifications.mark-as-read', $notificationId));

    $response->assertRedirect();
    $this->assertEquals(0, $this->user->unreadNotifications()->count());
});

test('users cannot mark other users notifications as read', function () {
    $this->actingAs($this->user);

    $notificationId = Str::uuid()->toString();

    // Notification belongs to otherUser
    DB::table('notifications')->insert([
        'id' => $notificationId,
        'type' => 'App\Notifications\SecurityEventNotification',
        'notifiable_type' => 'App\Models\User',
        'notifiable_id' => $this->otherUser->id,
        'data' => json_encode([
            'type' => 'security_event',
            'message' => 'Suspicious admin action',
        ]),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Submit mark as read request from user (Ahmed) for otherUser's (Khaled) notification
    $response = $this->post(route('notifications.mark-as-read', $notificationId));

    $response->assertStatus(403);
});

test('authorized users can mark all notifications as read', function () {
    $this->actingAs($this->user);

    for ($i = 0; $i < 3; $i++) {
        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\SecurityEventNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $this->user->id,
            'data' => json_encode([
                'type' => 'security_event',
                'message' => "Multiple events code: {$i}",
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->assertEquals(3, $this->user->unreadNotifications()->count());

    $response = $this->post(route('notifications.mark-all-as-read'));

    $response->assertRedirect();
    $this->assertEquals(0, $this->user->unreadNotifications()->count());
});
