<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class AdminAccountService
{
    public function admins(): LengthAwarePaginator
    {
        return User::query()
            ->where(fn ($query) => $query->where('is_admin', true)
                ->orWhereHas('roles', fn ($roles) => $roles
                    ->whereIn('name', [User::ADMIN_ROLE, User::TRANSACTION_PROCESSING_ADMIN_ROLE])
                    ->where('guard_name', 'web')))
            ->with('roles')
            ->latest('id')
            ->paginate(10);
    }

    /** @return array<string, string> */
    public function roles(): array
    {
        return [
            User::ADMIN_ROLE => 'Administrator penuh',
            User::TRANSACTION_PROCESSING_ADMIN_ROLE => 'Admin pemrosesan transaksi',
        ];
    }

    /** @param array{name: string, email: string, phone?: string|null, password: string, role: string} $data */
    public function create(array $data): User
    {
        $admin = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
        ]);
        $admin->save();
        $admin->assignRole(Role::findByName($data['role'], 'web'));

        return $admin;
    }
}
