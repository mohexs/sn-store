<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UserService
{
    public function paginate(string $search): LengthAwarePaginator
    {
        $search = trim($search);

        return User::query()->where('is_admin', false)
            ->whereDoesntHave('roles', fn (Builder $roles) => $roles
                ->whereIn('name', [User::ADMIN_ROLE, User::TRANSACTION_PROCESSING_ADMIN_ROLE])
                ->where('guard_name', 'web'))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('email', '%'.$search.'%')
                        ->orWhereLike('phone', '%'.$search.'%')
                        ->orWhereLike('city', '%'.$search.'%');
                });
            })
            ->latest('id')->paginate(10)->withQueryString();
    }

    public function assertCustomer(User $user): void
    {
        abort_if($user->is_admin || $user->hasAnyRole([User::ADMIN_ROLE, User::TRANSACTION_PROCESSING_ADMIN_ROLE]), 404);
    }

    /** @param array<string, mixed> $data */
    public function update(User $user, array $data): void
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }
}
