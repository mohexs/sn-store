<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

test('admin and customer reset requests deliver an email containing the reset link', function (bool $isAdmin) {
    $user = User::factory()->create(['is_admin' => $isAdmin]);

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHasNoErrors()->assertSessionHas('status');

    $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
    expect($messages)->toHaveCount(1);
    $email = $messages->first()->getOriginalMessage();
    expect($email->getTo()[0]->getAddress())->toBe($user->email);
    expect($email->getSubject())->toBe('Reset password akun Apotek Aja');
    expect($email->getHtmlBody())->toContain('/reset-password/')->toContain(urlencode($user->email))->toContain('60 menit');
})->with(['admin' => true, 'customer' => false]);

test('a reset link changes the password once and preserves the account role', function (bool $isAdmin) {
    $user = User::factory()->create(['is_admin' => $isAdmin]);
    $token = Password::createToken($user);
    $payload = ['token' => $token, 'email' => $user->email, 'password' => 'new-password123', 'password_confirmation' => 'new-password123'];

    $this->post(route('password.store'), $payload)->assertRedirect(route('login'))->assertSessionHasNoErrors();

    expect(Hash::check('new-password123', $user->fresh()->password))->toBeTrue();
    expect($user->fresh()->is_admin)->toBe($isAdmin);
    $this->post(route('password.store'), array_replace($payload, ['password' => 'another-password123', 'password_confirmation' => 'another-password123']))
        ->assertSessionHasErrors('email');
    expect(Hash::check('new-password123', $user->fresh()->password))->toBeTrue();
})->with(['admin' => true, 'customer' => false]);

test('expired reset links do not change passwords', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);
    $password = $user->password;
    $this->travel(61)->minutes();

    $this->post(route('password.store'), [
        'token' => $token, 'email' => $user->email, 'password' => 'new-password123', 'password_confirmation' => 'new-password123',
    ])->assertSessionHasErrors('email');

    expect($user->fresh()->password)->toBe($password);
});

test('a token cannot reset a different account', function () {
    $user = User::factory()->create();
    $other = User::factory()->admin()->create();
    $token = Password::createToken($user);
    $password = $other->password;

    $this->post(route('password.store'), [
        'token' => $token, 'email' => $other->email, 'password' => 'new-password123', 'password_confirmation' => 'new-password123',
    ])->assertSessionHasErrors('email');

    expect($other->fresh()->password)->toBe($password);
});

test('reset requests to unknown emails do not send notifications', function () {
    Notification::fake();

    $this->post(route('password.email'), ['email' => 'unknown@example.test'])->assertSessionHasErrors('email');

    Notification::assertNothingSent();
});

test('repeated reset requests are throttled without sending another email', function () {
    Notification::fake();
    $user = User::factory()->create();
    $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasNoErrors();

    $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasErrors('email');

    Notification::assertSentToTimes($user, ResetPassword::class, 1);
});
