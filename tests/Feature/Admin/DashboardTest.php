<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

it('redirects guests to login before showing the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('shows the shared public catalog summary to an authenticated user', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create(['name' => 'Pengelola SN Store']);
    $user->assignRole('admin');
    $products = config('storefront.products');
    config(['storefront.products' => [$products[0], $products[1], $products[4]]]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertViewIs('pages.admin.dashboard.index')
        ->assertViewHas('productCount', 3)
        ->assertViewHas('categoryCount', 2)
        ->assertViewHas('categories', fn ($categories) => $categories->all() === [
            ['name' => 'Vitamin & Suplemen', 'count' => 2, 'percentage' => 67.0],
            ['name' => 'Alat Kesehatan', 'count' => 1, 'percentage' => 33.0],
        ])
        ->assertSee('Pengelola SN Store')
        ->assertSee('Vitamin C 500 mg')
        ->assertSee('Vitamin D3 1000 IU')
        ->assertDontSee('Paracetamol 500 mg')
        ->assertSee('Pencatatan belum tersedia');
});

it('renders the dashboard empty states when the catalog is empty', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('admin');
    config(['storefront.products' => []]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertViewHas('productCount', 0)
        ->assertViewHas('categoryCount', 0)
        ->assertSee('Belum ada produk di katalog.')
        ->assertSee('Belum ada kategori dalam katalog.');
});

it('escapes account names displayed in the admin layout', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create(['name' => '<script>alert("unsafe")</script>']);
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee($user->name)
        ->assertDontSee($user->name, escape: false);
});
