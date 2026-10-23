<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertOk()
        ->assertSee('Buat akun baru')
        ->assertSee('action="'.route('register').'"', escape: false)
        ->assertSee('name="name"', escape: false)
        ->assertSee('name="email"', escape: false)
        ->assertSee('name="password_confirmation"', escape: false)
        ->assertSee('href="'.route('login').'"', escape: false)
        ->assertSee('focus:border-rose-700', escape: false);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home'));
    expect(auth()->user()->hasRole('customer'))->toBeTrue();
});
