<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(private CheckoutSummaryService $checkoutSummaryService) {}

    /** @param array<int, array{id: int, quantity: int}> $cart */
    public function summarize(array $cart): array
    {
        return $this->checkoutSummaryService->summarize($cart);
    }

    /**
     * @param  array<int, array{id: int, quantity: int}>  $cart
     * @param  array{customer_name: string, payment_method: string}  $data
     */
    public function createOrder(array $cart, array $data): Order
    {
        $summary = $this->checkoutSummaryService->summarize($cart);

        return DB::transaction(function () use ($data, $summary): Order {
            $order = Order::query()->create([
                'order_number' => 'SN-'.Str::upper(Str::random(12)),
                'customer_name' => $data['customer_name'],
                'sale_date' => now('Asia/Makassar')->toDateString(),
                'status' => 'pending',
                'subtotal_minor' => $summary['subtotal_minor'],
                'discount_minor' => $summary['discount_minor'],
                'total_minor' => $summary['total_minor'],
                'promotion_id' => $summary['promotion']?->id,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
            ]);

            $order->items()->createMany($summary['lines']->map(fn (array $line): array => [
                'product_id' => null,
                'product_name' => $line['name'],
                'product_sku' => $line['sku'],
                'quantity' => $line['quantity'],
                'unit_price_minor' => $line['price'] * 100,
                'discount_minor' => $line['discount_minor'],
            ])->all());

            return $order;
        });
    }

    public function forPayment(Order $order, string $sessionOrderNumber): Order
    {
        abort_unless(hash_equals($order->order_number, $sessionOrderNumber), 404);

        return $order->load(['items', 'promotion']);
    }
}
