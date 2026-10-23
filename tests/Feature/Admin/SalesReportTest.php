<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;

function createReportOrder(string $date, array $lines, string $status = 'paid', array $attributes = []): Order
{
    $order = Order::factory()->create(['sale_date' => $date, 'status' => $status, ...$attributes]);

    foreach ($lines as $line) {
        OrderItem::factory()->for($order)->create(['product_id' => null, ...$line]);
    }

    return $order;
}

it('requires authentication before showing sales reports', function () {
    $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
});

it('aggregates daily sales with inclusive dates and excludes unpaid orders', function () {
    $user = User::factory()->admin()->create();
    createReportOrder('2026-10-01', [
        ['quantity' => 2, 'unit_price_minor' => 1000, 'discount_minor' => 100],
        ['product_name' => 'Alat Kesehatan', 'product_sku' => 'SN-ALAT', 'quantity' => 1, 'unit_price_minor' => 5000],
    ]);
    createReportOrder('2026-10-02', [['quantity' => 3, 'unit_price_minor' => 1000]]);
    foreach (['pending', 'cancelled', 'refunded'] as $status) {
        createReportOrder('2026-10-01', [['unit_price_minor' => 999999]], $status);
    }
    createReportOrder('2026-10-03', [['unit_price_minor' => 999999]]);

    $response = $this->actingAs($user)->get(route('admin.reports.index', [
        'type' => 'daily', 'start_date' => '2026-10-01', 'end_date' => '2026-10-02',
    ]));

    $response->assertOk()->assertViewIs('pages.admin.reports.index')
        ->assertViewHas('summary', fn ($summary) => (int) $summary->net_minor === 9900
            && (int) $summary->gross_minor === 10000 && (int) $summary->discount_minor === 100
            && (int) $summary->orders_count === 2 && (int) $summary->quantity === 6)
        ->assertViewHas('rows', fn ($rows) => $rows->total() === 2
            && $rows->first()->sale_date === '2026-10-02' && (int) $rows->first()->net_minor === 3000);
});

it('groups sales per product and filters by name or SKU', function (string $keyword, int $net, int $units) {
    $user = User::factory()->admin()->create();
    createReportOrder('2026-10-01', [
        ['quantity' => 2, 'unit_price_minor' => 1000, 'discount_minor' => 100],
        ['product_name' => 'Termometer', 'product_sku' => 'SN-TERM', 'quantity' => 1, 'unit_price_minor' => 5000],
    ]);
    createReportOrder('2026-10-02', [['quantity' => 3, 'unit_price_minor' => 1000]]);

    $response = $this->actingAs($user)->get(route('admin.reports.index', [
        'type' => 'products', 'start_date' => '2026-10-01', 'end_date' => '2026-10-02', 'q' => $keyword,
    ]));

    $response->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && (int) $rows->first()->quantity === $units)
        ->assertViewHas('summary', fn ($summary) => (int) $summary->net_minor === $net);
})->with([
    'product name' => ['Vitamin C', 4900, 5],
    'product SKU' => ['SN-TERM', 5000, 1],
]);

it('sorts product sales by the selected measure', function (string $sort, array $expected) {
    $user = User::factory()->admin()->create();
    createReportOrder('2026-10-01', [
        ['product_name' => 'Alpha', 'product_sku' => 'A', 'quantity' => 1, 'unit_price_minor' => 5000],
        ['product_name' => 'Beta', 'product_sku' => 'B', 'quantity' => 3, 'unit_price_minor' => 1000],
    ]);

    $response = $this->actingAs($user)->get(route('admin.reports.index', [
        'type' => 'products', 'start_date' => '2026-10-01', 'end_date' => '2026-10-01', 'sort' => $sort,
    ]));

    $response->assertOk()->assertViewHas('rows', fn ($rows) => $rows->pluck('product_name')->all() === $expected);
})->with([
    'revenue' => ['revenue', ['Alpha', 'Beta']],
    'units' => ['quantity', ['Beta', 'Alpha']],
    'name' => ['name', ['Alpha', 'Beta']],
]);

it('shows all lines of an order and searches by number or customer', function (string $keyword) {
    $user = User::factory()->admin()->create();
    createReportOrder('2026-10-01', [
        ['product_name' => 'Produk pertama', 'quantity' => 2, 'unit_price_minor' => 1000],
        ['product_name' => 'Produk kedua', 'quantity' => 1, 'unit_price_minor' => 5000],
    ], attributes: ['order_number' => 'SN-PESAN-01', 'customer_name' => 'Pelanggan Satu']);
    createReportOrder('2026-10-02', [['unit_price_minor' => 999999]], attributes: ['customer_name' => 'Lainnya']);

    $response = $this->actingAs($user)->get(route('admin.reports.index', [
        'type' => 'orders', 'start_date' => '2026-10-01', 'end_date' => '2026-10-02', 'q' => $keyword,
    ]));

    $response->assertOk()->assertSee('SN-PESAN-01')->assertSee('Produk pertama')->assertSee('Produk kedua')
        ->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()->items->count() === 2)
        ->assertViewHas('summary', fn ($summary) => (int) $summary->orders_count === 1 && (int) $summary->net_minor === 7000);
})->with(['SN-PESAN-01', 'Pelanggan Satu']);

it('keeps the full filtered totals when visiting another results page', function () {
    $user = User::factory()->admin()->create();
    for ($day = 1; $day <= 16; $day++) {
        createReportOrder(sprintf('2026-10-%02d', $day), [['unit_price_minor' => 101]]);
    }

    $response = $this->actingAs($user)->get(route('admin.reports.index', [
        'type' => 'daily', 'start_date' => '2026-10-01', 'end_date' => '2026-10-16', 'page' => 2,
    ]));

    $response->assertOk()->assertViewHas('rows', fn ($rows) => $rows->count() === 1 && $rows->total() === 16)
        ->assertViewHas('summary', fn ($summary) => (int) $summary->net_minor === 1616 && (int) $summary->orders_count === 16);
});

it('keeps recorded names and prices after the current product changes or is deleted', function () {
    $user = User::factory()->admin()->create();
    $product = Product::factory()->create();
    createReportOrder('2026-10-01', [['product_id' => $product->id, 'product_name' => 'Nama saat transaksi', 'unit_price_minor' => 12345]]);
    $product->update(['name' => 'Nama sekarang', 'price' => 999999]);
    $product->delete();

    $response = $this->actingAs($user)->get(route('admin.reports.index', [
        'type' => 'products', 'start_date' => '2026-10-01', 'end_date' => '2026-10-01',
    ]));

    $response->assertOk()->assertSee('Nama saat transaksi')->assertDontSee('Nama sekarang')
        ->assertViewHas('summary', fn ($summary) => (int) $summary->net_minor === 12345);
});

it('renders empty reports without fabricated sales', function (string $type) {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->get(route('admin.reports.index', ['type' => $type]));

    $response->assertOk()->assertSee('Belum ada penjualan sesuai filter')
        ->assertViewHas('summary', fn ($summary) => (int) $summary->net_minor === 0 && (int) $summary->orders_count === 0);
})->with(['daily', 'products', 'orders']);

it('uses the current month in Makassar for the default report period', function () {
    $user = User::factory()->admin()->create();
    $this->travelTo(Carbon::parse('2026-10-10 18:00:00', 'UTC'));

    $response = $this->actingAs($user)->get(route('admin.reports.index'));

    $response->assertOk()->assertViewHas('filters', ['type' => 'daily', 'start_date' => '2026-10-01', 'end_date' => '2026-10-11']);
});

it('rejects invalid report filters', function (array $filters, string $field) {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->get(route('admin.reports.index', $filters));

    $response->assertRedirect()->assertSessionHasErrors($field);
})->with([
    'unknown report' => [['type' => 'untrusted'], 'type'],
    'invalid start' => [['start_date' => 'not-a-date'], 'start_date'],
    'invalid end' => [['end_date' => '2026-02-31'], 'end_date'],
    'reversed period' => [['start_date' => '2026-10-02', 'end_date' => '2026-10-01'], 'end_date'],
    'unsafe sort' => [['sort' => 'id desc; DROP TABLE orders'], 'sort'],
    'array search' => [['q' => ['untrusted']], 'q'],
    'long search' => [['q' => str_repeat('a', 101)], 'q'],
    'invalid page' => [['page' => 0], 'page'],
]);

it('escapes customer and product text in order details', function () {
    $user = User::factory()->admin()->create();
    $unsafe = '<script>alert("unsafe")</script>';
    createReportOrder('2026-10-01', [['product_name' => $unsafe]], attributes: ['customer_name' => $unsafe]);

    $response = $this->actingAs($user)->get(route('admin.reports.index', [
        'type' => 'orders', 'start_date' => '2026-10-01', 'end_date' => '2026-10-01',
    ]));

    $response->assertOk()->assertSee($unsafe)->assertDontSee($unsafe, escape: false);
});
