<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutCartRequest;
use App\Http\Requests\StoreCheckoutOrderRequest;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(CheckoutService $service): View
    {
        $cart = session('checkout.cart', []);

        return view('pages.public.checkout', [
            'summary' => $service->summarize($cart),
        ]);
    }

    public function storeCart(StoreCheckoutCartRequest $request): RedirectResponse
    {
        $request->session()->put('checkout.cart', $request->validated('cart'));

        return to_route('checkout.index');
    }

    public function storeOrder(StoreCheckoutOrderRequest $request, CheckoutService $service): RedirectResponse
    {
        $cart = $request->session()->get('checkout.cart', []);
        $order = $service->createOrder($cart, $request->safe()->only(['customer_name', 'payment_method']));

        $request->session()->forget('checkout.cart');
        $request->session()->put('checkout.order_number', $order->order_number);

        return to_route('checkout.payment', $order);
    }

    public function payment(Order $order, CheckoutService $service): View
    {
        return view('pages.public.payment', [
            'order' => $service->forPayment($order, (string) session('checkout.order_number', '')),
        ]);
    }
}
