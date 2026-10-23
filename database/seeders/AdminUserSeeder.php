<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = config('admin.name');
        $email = config('admin.email');
        $password = config('admin.password');

        if (! is_string($name) || trim($name) === '') {
            throw new RuntimeException('Set ADMIN_NAME to a non-empty value before seeding the admin user.');
        }

        if (! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Set ADMIN_EMAIL to a valid email address before seeding the admin user.');
        }

        if (! is_string($password) || mb_strlen($password) < 8) {
            throw new RuntimeException('Set ADMIN_PASSWORD to at least 8 characters before seeding the admin user.');
        }

        $this->call(RolePermissionSeeder::class);

        $admin = User::query()->firstOrCreate(
            ['email' => $email],
            ['name' => trim($name), 'password' => $password],
        );

        $admin->syncRoles(Role::findByName('admin', 'web'));
    }
}
