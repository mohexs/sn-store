<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

it('requires authentication for transactions', function () {
    $this->get(route('admin.transactions.index'))->assertRedirect(route('login'));
    $this->patch(route('admin.transactions.update', Order::factory()->create()), ['action' => 'pick'])->assertRedirect(route('login'));
});

it('shows paid orders by stage with search and transaction navigation', function () {
    $paid = Order::factory()->create(['status' => 'paid', 'order_number' => 'SN-PICK-001']);
    OrderItem::factory()->for($paid)->create();
    Order::factory()->create(['status' => 'pending', 'order_number' => 'SN-UNPAID']);
    $this->actingAs(User::factory()->admin()->create())->get(route('admin.transactions.index'))
        ->assertOk()->assertSee('SN-PICK-001')->assertDontSee('SN-UNPAID')->assertSee('Input Qty Proses')->assertSee('Konfirmasi picking');
    $this->get(route('admin.transactions.index', ['stage' => 'picking', 'q' => 'missing']))->assertOk()->assertDontSee('SN-PICK-001');
    foreach (['processing', 'packaging', 'shipping'] as $stage) {
        $this->get(route('admin.transactions.index', ['stage' => $stage]))->assertOk()->assertDontSee('SN-PICK-001');
    }
    $this->get(route('admin.transactions.index', ['stage' => 'invalid']))->assertNotFound();
});

it('processes an order through picking quantities packaging and shipment', function () {
    $order = Order::factory()->create(['status' => 'paid']);
    $item = OrderItem::factory()->for($order)->create(['quantity' => 3]);
    $this->actingAs(User::factory()->admin()->create());
    $url = route('admin.transactions.update', $order);
    $this->patch($url, ['action' => 'pick'])->assertSessionHasNoErrors();
    expect($order->fresh()->fulfillment_status)->toBe('processing');
    $this->patch($url, ['action' => 'save', 'quantities' => [$item->id => 1]])->assertSessionHasNoErrors();
    expect($item->fresh()->processed_quantity)->toBe(1);
    $this->get(route('admin.transactions.index', ['stage' => 'processing']))->assertOk()->assertSee($order->order_number)->assertSee('Simpan progres qty');
    $this->patch($url, ['action' => 'process', 'quantities' => [$item->id => 3]])->assertSessionHasNoErrors();
    expect($order->fresh()->fulfillment_status)->toBe('packaging');
    $this->get(route('admin.transactions.index', ['stage' => 'packaging']))->assertOk()->assertSee($order->order_number);
    $this->patch($url, ['action' => 'pack'])->assertSessionHasNoErrors();
    expect($order->fresh()->fulfillment_status)->toBe('shipping');
    $this->get(route('admin.transactions.index', ['stage' => 'shipping']))->assertOk()->assertSee('Nomor resi');
    $this->patch($url, ['action' => 'ship', 'shipping_carrier' => 'JNE', 'tracking_number' => 'SN12345'])->assertSessionHasNoErrors();
    expect($order->fresh())->fulfillment_status->toBe('shipped')->status->toBe('paid')->tracking_number->toBe('SN12345')->shipped_at->not->toBeNull();
    $this->get(route('admin.transactions.index', ['stage' => 'shipping']))->assertOk()->assertSee('SN12345')->assertSee('Sudah dikirim');
    $this->patch($url, ['action' => 'ship', 'shipping_carrier' => 'Other', 'tracking_number' => 'changed'])->assertSessionHasErrors('transaction');
    expect($order->fresh()->tracking_number)->toBe('SN12345');
});

it('rejects skipped stages unpaid orders invalid quantities and foreign items without partial writes', function () {
    $order = Order::factory()->create(['status' => 'paid']);
    $items = OrderItem::factory()->count(2)->for($order)->create(['quantity' => 3]);
    $foreign = OrderItem::factory()->create();
    $this->actingAs(User::factory()->admin()->create());
    $url = route('admin.transactions.update', $order);
    $this->patch($url, ['action' => 'pack'])->assertSessionHasErrors('transaction');
    $order->status = 'pending';
    $order->save();
    $this->patch($url, ['action' => 'pick'])->assertSessionHasErrors('transaction');
    $order->status = 'paid';
    $order->fulfillment_status = 'processing';
    $order->save();
    foreach ([
        [$items[0]->id => 3, $items[1]->id => 4],
        [$items[0]->id => 3, $items[1]->id => 1],
        [$items[0]->id => 3],
        [$items[0]->id => 3, $foreign->id => 3],
        [$items[0]->id => -1, $items[1]->id => 3],
        [$items[0]->id => 1.5, $items[1]->id => 3],
    ] as $quantities) {
        $this->patch($url, ['action' => 'process', 'quantities' => $quantities])->assertSessionHasErrors();
        expect($order->fresh()->fulfillment_status)->toBe('processing');
        expect($items[0]->fresh()->processed_quantity)->toBe(0);
    }
    $order->fulfillment_status = 'shipping';
    $order->save();
    $this->patch($url, ['action' => 'ship'])->assertSessionHasErrors(['shipping_carrier', 'tracking_number']);
    $this->patch($url, ['action' => 'ship', 'shipping_carrier' => 'JNE', 'tracking_number' => '123'])->assertSessionHasErrors('transaction');
});
