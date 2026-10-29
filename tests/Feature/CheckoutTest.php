<?php

use App\Models\Order;
use App\Models\Promotion;

it('shows an empty cart message when checkout has no cart in the session', function () {
    $response = $this->get(route('checkout.index'));

    $response->assertOk()
        ->assertSee('Keranjang Anda masih kosong')
        ->assertDontSee('Buat pesanan &amp; lanjut bayar');
});

it('redirects to checkout with an error when creating an order with an empty cart', function () {
    $response = $this->post(route('checkout.order'), [
        'customer_name' => 'Dina Pembeli',
        'payment_method' => 'bank_transfer',
    ]);

    $response->assertRedirect(route('checkout.index'))
        ->assertSessionHasErrors(['cart' => 'Keranjang belanja kosong. Silakan pilih produk terlebih dahulu.']);
    $this->assertDatabaseCount('orders', 0);
});

it('validates cart items and calculates the best active global promotion', function () {
    $this->travelTo('2026-03-01 12:00:00');
    Promotion::query()->create([
        'name' => 'Potongan kecil',
        'discount_type' => 'fixed',
        'discount_value' => 10000,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);
    Promotion::query()->create([
        'name' => 'Diskon terbaik',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);
    Promotion::query()->create([
        'name' => 'Belum aktif',
        'discount_type' => 'fixed',
        'discount_value' => 100000,
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDays(2),
    ]);

    $this->post(route('checkout.cart'), [
        'cart_payload' => json_encode([
            ['id' => 1, 'quantity' => 1],
            ['id' => 2, 'quantity' => 2],
        ]),
    ])->assertRedirect(route('checkout.index'));

    $response = $this->get(route('checkout.index'));

    $response->assertOk()
        ->assertSee('Vitamin C 500 mg')
        ->assertSee('Diskon terbaik')
        ->assertSee('Rp175.000')
        ->assertSee('−Rp35.000')
        ->assertSee('Rp140.000');
});

it('rejects unknown or tampered product details in the cart', function () {
    $response = $this->from(route('home'))->post(route('checkout.cart'), [
        'cart_payload' => json_encode([
            ['id' => 999, 'quantity' => 1],
        ]),
    ]);

    $response->assertRedirect(route('home'))
        ->assertSessionHasErrors('cart.0.id');
    expect(session('checkout.cart'))->toBeNull();
});

it('creates a pending manual payment order with server-priced items and the active discount', function () {
    $this->travelTo('2026-03-01 12:00:00');
    $promotion = Promotion::query()->create([
        'name' => 'Promo 10 persen',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);
    $this->post(route('checkout.cart'), [
        'cart_payload' => json_encode([
            ['id' => 1, 'quantity' => 1],
            ['id' => 3, 'quantity' => 1],
        ]),
    ])->assertRedirect(route('checkout.index'));

    $response = $this->post(route('checkout.order'), [
        'customer_name' => 'Dina Pembeli',
        'payment_method' => 'bank_transfer',
    ]);

    $order = Order::query()->sole();
    $response->assertRedirect(route('checkout.payment', $order));
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'customer_name' => 'Dina Pembeli',
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
        'payment_status' => 'pending',
        'promotion_id' => $promotion->id,
        'subtotal_minor' => 5700000,
        'discount_minor' => 570000,
        'total_minor' => 5130000,
    ]);
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_name' => 'Vitamin C 500 mg',
        'product_sku' => 'DEMO-000001',
        'unit_price_minor' => 4500000,
        'discount_minor' => 450000,
    ]);
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_name' => 'Paracetamol 500 mg',
        'unit_price_minor' => 1200000,
        'discount_minor' => 120000,
    ]);

    $this->get(route('checkout.payment', $order))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Menunggu pembayaran')
        ->assertSee('Rp51.300');
    expect(session('checkout.cart'))->toBeNull();
});

it('does not expose a payment page to a different session', function () {
    $order = Order::factory()->create([
        'order_number' => 'SN-PRIVATE-123',
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $this->withSession(['checkout.order_number' => 'SN-OTHER-456'])
        ->get(route('checkout.payment', $order))
        ->assertNotFound();
});
