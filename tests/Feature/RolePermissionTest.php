<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

it('seeds admin and customer roles with the expected permissions', function () {
    $legacyAdmin = User::factory()->admin()->create();

    $this->seed(RolePermissionSeeder::class);

    $adminRole = Role::findByName('admin');
    $customerRole = Role::findByName('customer');

    expect($adminRole->hasPermissionTo('manage customers'))->toBeTrue()
        ->and($adminRole->hasPermissionTo('manage products'))->toBeTrue()
        ->and($adminRole->hasPermissionTo('access admin'))->toBeTrue()
        ->and($customerRole->permissions)->toBeEmpty()
        ->and($legacyAdmin->fresh()->hasRole('admin'))->toBeTrue();
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
