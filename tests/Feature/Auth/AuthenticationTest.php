<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertSee('Masuk ke akun Anda')
        ->assertSee('action="'.route('login').'"', escape: false)
        ->assertSee('name="remember"', escape: false)
        ->assertSee('href="'.route('password.request').'"', escape: false)
        ->assertSee('href="'.route('register').'"', escape: false)
        ->assertSee('focus:border-rose-700', escape: false);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home'));
});

test('admins can authenticate and reach the dashboard', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('customers are redirected to the public home page after requesting the dashboard', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('customer');

    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('home'));
    $this->actingAs($user)->get(route('home'))->assertOk();
    $this->get(route('dashboard'))->assertForbidden();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
