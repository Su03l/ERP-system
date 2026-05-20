<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the login screen successfully', function () {
    $response = $this->get('/login');

    $response->assertSuccessful()
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false);
});

it('authenticates a user with correct credentials and redirects to dashboard', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

it('fails authentication with incorrect credentials and displays errors', function () {
    $company = Company::factory()->create();
    User::factory()->for($company)->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('renders the forgot password screen', function () {
    $response = $this->get('/forgot-password');

    $response->assertSuccessful()
        ->assertSee('name="email"', false);
});

it('simulates forgot password reset link request', function () {
    $response = $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertSessionHas('success');
});

it('renders the reset password screen', function () {
    $response = $this->get('/reset-password/sample-token?email=test@example.com');

    $response->assertSuccessful()
        ->assertSee('name="password"', false)
        ->assertSee('name="password_confirmation"', false);
});

it('simulates updating the password successfully', function () {
    $response = $this->post('/reset-password', [
        'token' => 'sample-token',
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertRedirect('/login')
        ->assertSessionHas('success');
});

it('allows an authenticated user to log out', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $response = $this->actingAs($user)
        ->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});
