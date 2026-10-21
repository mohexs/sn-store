<?php

use App\Models\User;

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('shows a login link in the public navbar for guests', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('href="'.route('login').'"', escape: false)
        ->assertSeeText('Masuk')
        ->assertDontSeeText('Dashboard');
});

it('shows a dashboard link in the public navbar for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertSee('href="'.route('dashboard').'"', escape: false)
        ->assertSeeText('Dashboard');
});
