<?php

namespace App\Services;

use App\Models\User;

class ProfileService
{
    /** @param array<string, mixed> $data */
    public function update(User $user, array $data): void
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
