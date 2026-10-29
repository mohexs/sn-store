<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

it('seeds the full admin, transaction processing admin, and customer roles with expected permissions', function () {
    $legacyAdmin = User::factory()->admin()->create();

    $this->seed(RolePermissionSeeder::class);

    $adminRole = Role::findByName('admin');
    $customerRole = Role::findByName('customer');
    $transactionProcessingAdminRole = Role::findByName(User::TRANSACTION_PROCESSING_ADMIN_ROLE);

    expect($adminRole->hasPermissionTo('manage customers'))->toBeTrue()
        ->and($adminRole->hasPermissionTo('manage products'))->toBeTrue()
        ->and($adminRole->hasPermissionTo('manage promotions'))->toBeTrue()
        ->and($adminRole->hasPermissionTo('manage ads'))->toBeTrue()
        ->and($adminRole->hasPermissionTo('access admin'))->toBeTrue()
        ->and($transactionProcessingAdminRole->permissions->pluck('name')->sort()->values()->all())->toBe([
            'access admin',
            'manage customers',
            'manage products',
            'manage transactions',
            'view products',
            'view themes',
            'view transactions',
        ])
        ->and($customerRole->permissions)->toBeEmpty()
        ->and($legacyAdmin->fresh()->hasRole('admin'))->toBeTrue();
});

it('limits transaction processing admins to their assigned menu and route permissions', function () {
    $this->seed(RolePermissionSeeder::class);
    $processor = User::factory()->create();
    $processor->assignRole(User::TRANSACTION_PROCESSING_ADMIN_ROLE);
    $customer = User::factory()->create(['email' => 'processor-test-customer@example.test']);

    $this->actingAs($processor)->get(route('dashboard'))->assertOk()
        ->assertSee(route('admin.transactions.index'))
        ->assertSee(route('admin.themes.index'))
        ->assertSee(route('admin.users.index'))
        ->assertSee(route('admin.products.index'))
        ->assertDontSee(route('admin.categories.index'))
        ->assertDontSee(route('admin.promotions.index'))
        ->assertDontSee(route('admin.ads.index'))
        ->assertDontSee(route('admin.reports.index'))
        ->assertDontSee(route('admin.admin-accounts.index'));

    $this->get(route('admin.transactions.index'))->assertOk();
    $this->get(route('admin.themes.index'))->assertOk();
    $this->get(route('admin.users.index'))->assertOk()
        ->assertSee($customer->email)
        ->assertViewHas('users', fn ($users) => $users->contains($customer) && ! $users->contains($processor));
    $this->get(route('admin.users.edit', $processor))->assertNotFound();
    $this->get(route('admin.products.index'))->assertOk();
    $this->get(route('admin.products.create'))->assertOk();
    $this->get(route('admin.categories.index'))->assertForbidden();
    $this->get(route('admin.promotions.index'))->assertForbidden();
    $this->get(route('admin.ads.index'))->assertForbidden();
    $this->get(route('admin.reports.index'))->assertForbidden();
    $this->get(route('admin.admin-accounts.index'))->assertForbidden();
});

it('allows only the permissions assigned to a role on admin routes', function () {
    $this->seed(RolePermissionSeeder::class);
    $role = Role::create(['name' => 'catalog-reader', 'guard_name' => 'web']);
    $role->givePermissionTo('view categories');
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get(route('admin.categories.index'))->assertOk();
    $this->actingAs($user)->post(route('admin.categories.store'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.products.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.promotions.index'))->assertForbidden();
});

it('authorizes admin role users without the legacy admin flag', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $this->get(route('admin.categories.index'))->assertOk();
});

it('keeps customers on public pages while allowing their own profile', function () {
    $this->seed(RolePermissionSeeder::class);
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $this->actingAs($customer)->get(route('home'))->assertOk();
    $this->get(route('profile.edit'))->assertOk();
    $this->get(route('dashboard'))->assertForbidden();
    $this->get(route('admin.categories.index'))->assertForbidden();
});

it('keeps role-based administrators out of customer management', function () {
    $this->seed(RolePermissionSeeder::class);
    $legacyAdmin = User::factory()->admin()->create();
    $roleAdmin = User::factory()->create();
    $roleAdmin->assignRole('admin');

    $this->actingAs($legacyAdmin)->get(route('admin.users.index'))
        ->assertOk()
        ->assertDontSee($roleAdmin->email)
        ->assertViewHas('users', fn ($users) => $users->isEmpty());
    $this->get(route('admin.admin-accounts.index'))->assertOk()->assertSee($roleAdmin->email);
    $this->get(route('admin.users.edit', $roleAdmin))->assertNotFound();
});
