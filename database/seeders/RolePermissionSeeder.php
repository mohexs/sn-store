<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect([
            'access admin',
            'view categories',
            'manage categories',
            'view products',
            'manage products',
            'view promotions',
            'manage promotions',
            'view ads',
            'manage ads',
            'view transactions',
            'manage transactions',
            'view reports',
            'view themes',
            'manage admin accounts',
            'manage customers',
        ])->map(fn (string $name): Permission => Permission::findOrCreate($name, 'web'));

        $adminRole = Role::findOrCreate(User::ADMIN_ROLE, 'web');
        $adminRole->syncPermissions($permissions);

        $transactionProcessingAdminRole = Role::findOrCreate(User::TRANSACTION_PROCESSING_ADMIN_ROLE, 'web');
        $transactionProcessingAdminRole->syncPermissions([
            'access admin',
            'view products',
            'manage products',
            'view transactions',
            'manage transactions',
            'view themes',
            'manage customers',
        ]);

        $customerRole = Role::findOrCreate('customer', 'web');
        $customerRole->syncPermissions([]);

        User::query()
            ->where('is_admin', true)
            ->eachById(fn (User $user) => $user->assignRole($adminRole));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
