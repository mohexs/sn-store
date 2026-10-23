<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TransactionRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public const STAGES = [
        'picking' => 'Picking Barang',
        'processing' => 'Input Qty Proses',
        'packaging' => 'Packaging',
        'shipping' => 'Proses Pengiriman',
    ];

    public function index(Request $request, string $stage = 'picking'): View
    {
        abort_unless(isset(self::STAGES[$stage]), 404);
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1']]);
        $query = Order::query()->where('status', 'paid');
        $counts = (clone $query)->selectRaw('fulfillment_status, COUNT(*) as total')->groupBy('fulfillment_status')->pluck('total', 'fulfillment_status');
        $query->whereIn('fulfillment_status', $stage === 'shipping' ? ['shipping', 'shipped'] : [$stage]);
        if ($keyword = trim($filters['q'] ?? '')) {
            $query->where(fn ($query) => $query->where('order_number', 'like', '%'.$keyword.'%')->orWhere('customer_name', 'like', '%'.$keyword.'%'));
        }

        return view('pages.admin.transactions.index', [
            'stage' => $stage, 'stages' => self::STAGES, 'counts' => $counts,
            'orders' => $query->with('items')->orderBy('sale_date')->orderBy('id')->paginate(10)->withQueryString(),
            'keyword' => $filters['q'] ?? '',
        ]);
    }

    public function update(TransactionRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();
        $stage = DB::transaction(function () use ($data, $order): string {
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

        return to_route('admin.transactions.index', ['stage' => $stage])->with('success', 'Transaksi '.$order->order_number.' berhasil disimpan.');
    }
}
