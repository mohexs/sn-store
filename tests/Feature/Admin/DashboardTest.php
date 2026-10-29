<?php

use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Carbon;

it('redirects guests to login before showing the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('shows the shared public catalog summary to an authenticated user', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create(['name' => 'Pengelola Apotek Aja']);
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
        ->assertSee('Pengelola Apotek Aja')
        ->assertSee('Vitamin C 500 mg')
        ->assertSee('Vitamin D3 1000 IU')
        ->assertDontSee('Paracetamol 500 mg')
        ->assertSee('Penghasilan dari pesanan lunas');
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

it('defaults order and revenue summaries to today in Makassar', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->travelTo(Carbon::parse('2026-10-25 18:00:00', 'UTC'));
    Order::factory()->create(['sale_date' => '2026-10-26', 'status' => 'paid', 'total_minor' => 10000]);
    Order::factory()->create(['sale_date' => '2026-10-25', 'status' => 'paid', 'total_minor' => 12500]);
    Order::factory()->create(['sale_date' => '2026-10-25', 'status' => 'pending', 'total_minor' => 99000]);
    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertViewHas('filters', ['start_date' => '2026-10-26', 'end_date' => '2026-10-26'])
        ->assertViewHas('orderCount', 1)
        ->assertViewHas('revenueMinor', 10000)
        ->assertSee('Total penghasilan')
        ->assertSee('Tanggal mulai')
        ->assertSee('Tanggal akhir')
        ->assertSee('26/10/2026')
        ->assertSee('name="start_date" type="text"', escape: false)
        ->assertSee('placeholder="dd/mm/yyyy"', escape: false);
});

it('filters order count and paid revenue by the selected inclusive date range', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('admin');
    Order::factory()->create(['sale_date' => '2026-10-01', 'status' => 'paid', 'total_minor' => 12345]);
    Order::factory()->create(['sale_date' => '2026-10-02', 'status' => 'paid', 'total_minor' => 25000]);
    Order::factory()->create(['sale_date' => '2026-10-02', 'status' => 'pending', 'total_minor' => 99000]);
    Order::factory()->create(['sale_date' => '2026-10-03', 'status' => 'paid', 'total_minor' => 50000]);

    $response = $this->actingAs($user)->get(route('dashboard', [
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-02',
    ]));

    $response->assertOk()
        ->assertViewHas('filters', ['start_date' => '2026-10-01', 'end_date' => '2026-10-02'])
        ->assertViewHas('orderCount', 3)
        ->assertViewHas('revenueMinor', 37345)
        ->assertSee('Rp373,45');
});

it('rejects invalid dashboard date ranges', function (array $filters, string $field) {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get(route('dashboard', $filters));

    $response->assertRedirect()->assertSessionHasErrors($field);
})->with([
    'invalid start date' => [['start_date' => '2026-02-31'], 'start_date'],
    'end before start' => [['start_date' => '2026-10-02', 'end_date' => '2026-10-01'], 'end_date'],
]);

it('escapes account names displayed in the admin layout', function () {
    $this->seed(RolePermissionSeeder::class);
    $user = User::factory()->create(['name' => '<script>alert("unsafe")</script>']);
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee($user->name)
        ->assertDontSee($user->name, escape: false);
});
