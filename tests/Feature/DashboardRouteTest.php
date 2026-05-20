<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires authentication to access the dashboard', function () {
    $this->get('/dashboard')
        ->assertRedirect('/login');
});

it('renders the dashboard successfully for authenticated users with correct Arabic RTL direction by default', function () {
    $company = Company::factory()->create(['locale' => 'ar']);
    $user = User::factory()->for($company)->create(['preferred_locale' => 'ar']);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertSee('dir="rtl"', false)
        ->assertSee('lang="ar"', false);
});

it('renders the dashboard successfully in English LTR direction when requested', function () {
    $company = Company::factory()->create(['locale' => 'en']);
    $user = User::factory()->for($company)->create(['preferred_locale' => 'en']);

    $this->actingAs($user)
        ->get('/dashboard?locale=en')
        ->assertSuccessful()
        ->assertSee('dir="ltr"', false)
        ->assertSee('lang="en"', false);
});
