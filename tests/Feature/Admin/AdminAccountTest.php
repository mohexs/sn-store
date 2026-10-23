<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

it('requires login for admin account management', function (string $method, string $path) {
    $this->call($method, $path)->assertRedirect(route('login'));
})->with([['GET', '/admin/admin-accounts'], ['GET', '/admin/admin-accounts/create'], ['POST', '/admin/admin-accounts']]);

it('denies customers access to admin account management', function (string $method, string $path) {
    $customer = User::factory()->create();

    $this->actingAs($customer)->call($method, $path, [
        'name' => 'Unauthorized', 'email' => 'unauthorized@example.test',
        'password' => 'password123', 'password_confirmation' => 'password123',
    ])->assertForbidden();

    $this->assertDatabaseMissing('users', ['email' => 'unauthorized@example.test']);
})->with([['GET', '/admin/admin-accounts'], ['GET', '/admin/admin-accounts/create'], ['POST', '/admin/admin-accounts']]);

it('lists only admin accounts and escapes their names', function () {
    $admin = User::factory()->admin()->create(['name' => '<script>admin</script>']);
    $customer = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.admin-accounts.index'))->assertOk()
        ->assertSee($admin->email)->assertSee($admin->name)->assertDontSee($admin->name, escape: false)
        ->assertDontSee($customer->email)->assertSee(route('admin.admin-accounts.create'));
});

it('shows the admin creation form', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.admin-accounts.create'))->assertOk()
        ->assertSee('Konfirmasi password')->assertSee(route('admin.admin-accounts.store'));
});

it('creates an admin with a hashed password while keeping the current admin logged in', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.admin-accounts.store'), [
        'name' => 'Admin Baru', 'email' => 'newadmin@example.test', 'phone' => '08123456789',
        'password' => 'secure-password123', 'password_confirmation' => 'secure-password123',
        'is_admin' => false, 'email_verified_at' => now(),
    ])->assertRedirect(route('admin.admin-accounts.index'))->assertSessionHasNoErrors()->assertSessionHas('status');

    $created = User::query()->where('email', 'newadmin@example.test')->firstOrFail();
    $this->assertDatabaseHas('users', ['id' => $created->id, 'name' => 'Admin Baru', 'phone' => '08123456789', 'is_admin' => false, 'email_verified_at' => null]);
    expect(Hash::check('secure-password123', $created->password))->toBeTrue();
    expect($created->hasRole('admin'))->toBeTrue();
    $this->assertAuthenticatedAs($admin);
    $this->actingAs($admin)->get(route('admin.users.index'))->assertDontSee('newadmin@example.test');
});

it('allows creating an admin without a phone number and logging in with that account', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->post(route('admin.admin-accounts.store'), [
        'name' => 'Admin Tanpa Telepon', 'email' => 'admin-login@example.test',
        'password' => 'secure-password123', 'password_confirmation' => 'secure-password123',
    ])->assertRedirect()->assertSessionHasNoErrors();
    $this->post(route('logout'));

    $this->post(route('login'), ['email' => 'admin-login@example.test', 'password' => 'secure-password123'])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->get(route('admin.admin-accounts.index'))->assertOk();
});

it('rejects invalid admin details without creating an account', function (array $invalid, string $field) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.admin-accounts.store'), array_replace([
        'name' => 'New Admin', 'email' => 'newadmin@example.test',
        'password' => 'secure-password123', 'password_confirmation' => 'secure-password123',
    ], $invalid))->assertSessionHasErrors($field);

    $this->assertDatabaseCount('users', 1);
})->with([
    'blank name' => [['name' => ' '], 'name'],
    'long name' => [['name' => str_repeat('a', 256)], 'name'],
    'missing email' => [['email' => ''], 'email'],
    'invalid email' => [['email' => 'invalid'], 'email'],
    'uppercase email' => [['email' => 'ADMIN@example.test'], 'email'],
    'invalid phone' => [['phone' => 'not a number'], 'phone'],
    'long phone' => [['phone' => str_repeat('1', 31)], 'phone'],
    'missing password' => [['password' => ''], 'password'],
    'short password' => [['password' => 'short', 'password_confirmation' => 'short'], 'password'],
    'long password' => [['password' => str_repeat('a', 256), 'password_confirmation' => str_repeat('a', 256)], 'password'],
    'mismatched confirmation' => [['password_confirmation' => 'different-password'], 'password'],
    'missing confirmation' => [['password_confirmation' => ''], 'password'],
]);

it('rejects an email already registered as an admin or customer', function (bool $isAdmin) {
    $admin = User::factory()->admin()->create();
    $existing = User::factory()->create(['is_admin' => $isAdmin]);

    $this->actingAs($admin)->post(route('admin.admin-accounts.store'), [
        'name' => 'Duplicate', 'email' => $existing->email,
        'password' => 'secure-password123', 'password_confirmation' => 'secure-password123',
    ])->assertSessionHasErrors(['email' => 'Email sudah digunakan akun lain.']);

    $this->assertDatabaseCount('users', 2);
})->with([true, false]);

it('does not allow public registration to grant admin privileges', function () {
    $this->post(route('register'), [
        'name' => 'Customer', 'email' => 'customer-register@example.test',
        'password' => 'password123', 'password_confirmation' => 'password123', 'is_admin' => true,
    ])->assertRedirect();

    $customer = User::query()->where('email', 'customer-register@example.test')->firstOrFail();
    $this->assertDatabaseHas('users', ['email' => 'customer-register@example.test', 'is_admin' => false]);
    expect($customer->hasRole('customer'))->toBeTrue();
});
