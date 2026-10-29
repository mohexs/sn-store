<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;

class EmailVerificationService
{
    public function send(User $user): void
    {
        $user->sendEmailVerificationNotification();
    }

    public function verify(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            return true;
        }

        return false;
    }
}
