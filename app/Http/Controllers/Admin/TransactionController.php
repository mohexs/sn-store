<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TransactionIndexRequest;
use App\Http\Requests\Admin\TransactionRequest;
use App\Models\Order;
use App\Services\Admin\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(TransactionIndexRequest $request, TransactionService $service, string $stage = 'picking'): View
    {
        return view('pages.admin.transactions.index', $service->listing($request->validated(), $stage));
    }

    public function confirmPayment(Order $order, TransactionService $service): RedirectResponse
    {
        $service->confirmPayment($order);

        return to_route('admin.transactions.index')->with('success', 'Pembayaran '.$order->order_number.' berhasil dikonfirmasi.');
    }

    public function update(TransactionRequest $request, Order $order, TransactionService $service): RedirectResponse
    {
        $stage = $service->update($order, $request->validated());

        return to_route('admin.transactions.index', ['stage' => $stage])->with('success', 'Transaksi '.$order->order_number.' berhasil disimpan.');
    }
}
