<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('shows a login link in the public navbar for guests', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('href="'.route('login').'"', escape: false)
        ->assertSee('href="'.route('register').'"', escape: false)
        ->assertSeeText('Masuk')
        ->assertSeeText('Daftar')
        ->assertDontSeeText('Dashboard');
});

it('shows a dashboard link and logout form in the public navbar for admins', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertSee('href="'.route('dashboard').'"', escape: false)
        ->assertSeeText('Dashboard')
        ->assertSee('action="'.route('logout').'"', escape: false)
        ->assertSeeText('Keluar');
});

it('hides the dashboard link and shows logout for customers on the public page', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('customer');

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertDontSee('href="'.route('dashboard').'"', escape: false)
        ->assertDontSeeText('Dashboard')
        ->assertSee('action="'.route('logout').'"', escape: false)
        ->assertSeeText('Keluar');
});
