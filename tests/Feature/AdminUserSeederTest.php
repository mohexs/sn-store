<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

it('creates the configured admin user and assigns the admin role', function () {
    config([
        'admin.name' => 'Admin Apotek Aja',
        'admin.email' => 'admin@sn-store.test',
        'admin.password' => 'secure-admin-password',
    ]);

    app(AdminUserSeeder::class)->run();

    $admin = User::query()->where('email', 'admin@sn-store.test')->firstOrFail();

    $this->assertModelExists($admin);
    expect($admin->name)->toBe('Admin Apotek Aja')
        ->and(Hash::check('secure-admin-password', $admin->password))->toBeTrue()
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and($admin->is_admin)->toBeFalse();
});

it('preserves the existing admin password when the seeder runs again', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create([
        'email' => 'admin@sn-store.test',
        'password' => 'existing-admin-password',
    ]);
    $admin->assignRole('customer');
    config([
        'admin.name' => 'Admin Apotek Aja',
        'admin.email' => 'admin@sn-store.test',
        'admin.password' => 'replacement-admin-password',
    ]);

    app(AdminUserSeeder::class)->run();

    $admin->refresh();
    expect(Hash::check('existing-admin-password', $admin->password))->toBeTrue()
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and($admin->hasRole('customer'))->toBeFalse();
    $this->assertDatabaseCount('users', 1);
});

it('requires a configured secure admin password before creating a user', function () {
    config([
        'admin.name' => 'Admin Apotek Aja',
        'admin.email' => 'admin@sn-store.test',
        'admin.password' => '',
    ]);

    expect(fn () => app(AdminUserSeeder::class)->run())
        ->toThrow(RuntimeException::class, 'Set ADMIN_PASSWORD to at least 8 characters');

    $this->assertDatabaseCount('users', 0);
});
