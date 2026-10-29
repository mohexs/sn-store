<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class AuthenticationService
{
    /** @param array{name: string, email: string, password: string} $data */
    public function register(array $data): User
    {
        $user = User::create($data);
        $user->assignRole(Role::findOrCreate('customer', 'web'));
        event(new Registered($user));
        Auth::login($user);

        return $user;
    }
}
