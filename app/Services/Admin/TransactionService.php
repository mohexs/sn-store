<?php

namespace App\Services\Admin;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public const STAGES = [
        'picking' => 'Picking Barang',
        'processing' => 'Input Qty Proses',
        'packaging' => 'Packaging',
        'shipping' => 'Proses Pengiriman',
    ];

    /**
     * @param  array{q?: string|null}  $filters
     * @return array{stage: string, stages: array<string, string>, counts: \Illuminate\Support\Collection, orders: LengthAwarePaginator, pendingPayments: Collection, keyword: string}
     */
    public function listing(array $filters, string $stage): array
    {
        abort_unless(isset(self::STAGES[$stage]), 404);
        $query = Order::query()->where('status', 'paid');
        $counts = (clone $query)->selectRaw('fulfillment_status, COUNT(*) as total')
            ->groupBy('fulfillment_status')->pluck('total', 'fulfillment_status');
        $query->whereIn('fulfillment_status', $stage === 'shipping' ? ['shipping', 'shipped'] : [$stage]);
        $keyword = trim($filters['q'] ?? '');

        if ($keyword !== '') {
            $query->where(fn ($query) => $query
                ->where('order_number', 'like', '%'.$keyword.'%')
                ->orWhere('customer_name', 'like', '%'.$keyword.'%'));
        }

        return [
            'stage' => $stage,
            'stages' => self::STAGES,
            'counts' => $counts,
            'orders' => $query->with('items')->orderBy('sale_date')->orderBy('id')->paginate(10)->withQueryString(),
            'pendingPayments' => $stage === 'picking'
                ? Order::query()->where('status', 'pending')->where('payment_status', 'pending')
                    ->with('items')->orderBy('sale_date')->orderBy('id')->limit(10)->get()
                : new Collection,
            'keyword' => $filters['q'] ?? '',
        ];
    }

    public function confirmPayment(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status !== 'pending' || $lockedOrder->payment_status !== 'pending' || $lockedOrder->payment_method !== 'bank_transfer') {
                throw ValidationException::withMessages(['payment' => 'Pesanan ini tidak menunggu konfirmasi pembayaran.']);
            }

            $lockedOrder->status = 'paid';
            $lockedOrder->payment_status = 'paid';
            $lockedOrder->save();
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Order $order, array $data): string
    {
        return DB::transaction(function () use ($data, $order): string {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $expected = match ($data['action']) {
                'pick' => 'picking', 'save', 'process' => 'processing', 'pack' => 'packaging', 'ship' => 'shipping',
            };

            if ($order->status !== 'paid' || $order->fulfillment_status !== $expected) {
                throw ValidationException::withMessages(['transaction' => 'Status pesanan sudah berubah atau belum lunas. Muat ulang halaman sebelum melanjutkan.']);
            }

            $items = $order->items()->lockForUpdate()->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['transaction' => 'Pesanan belum memiliki barang untuk diproses.']);
            }

            if (in_array($data['action'], ['save', 'process'], true)) {
                $quantities = $data['quantities'];

                if (count($quantities) !== $items->count() || array_diff(array_keys($quantities), $items->modelKeys())) {
                    throw ValidationException::withMessages(['quantities' => 'Isi qty seluruh barang dari pesanan ini saja.']);
                }

                foreach ($items as $item) {
                    $quantity = $quantities[$item->id] ?? null;

                    if ($quantity === null || $quantity > $item->quantity || ($data['action'] === 'process' && (int) $quantity !== $item->quantity)) {
                        throw ValidationException::withMessages(['quantities.'.$item->id => 'Qty tidak boleh melebihi pesanan dan harus lengkap sebelum masuk packaging.']);
                    }

                    $item->processed_quantity = (int) $quantity;
                    $item->save();
                }
            }

            if (in_array($data['action'], ['pack', 'ship'], true) && $items->contains(fn ($item) => $item->processed_quantity !== $item->quantity)) {
                throw ValidationException::withMessages(['transaction' => 'Qty seluruh barang harus lengkap sebelum melanjutkan.']);
            }

            $order->fulfillment_status = match ($data['action']) {
                'pick' => 'processing', 'save' => 'processing', 'process' => 'packaging', 'pack' => 'shipping', 'ship' => 'shipped',
            };

            if ($data['action'] === 'ship') {
                $order->shipping_carrier = $data['shipping_carrier'];
                $order->tracking_number = $data['tracking_number'];
                $order->shipped_at = now();
            }

            $order->save();

            return $expected;
        });
    }
}
