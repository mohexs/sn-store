<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordService
{
    public function sendResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /** @param array{email: string, password: string, token: string} $data */
    public function reset(array $data): string
    {
        return Password::reset($data, function (User $user) use ($data): void {
            $user->forceFill([
                'password' => Hash::make($data['password']),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });
    }

    public function update(User $user, string $password): void
    {
        $user->update(['password' => Hash::make($password)]);
    }
}
