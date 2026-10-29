<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $storeName }} — jelajahi obat, vitamin, dan alat kesehatan untuk kebutuhan sehari-hari Anda.">
    <meta name="theme-color" content="#b92738">
    <title>@yield('title', $storeName.' — Teman Hidup Sehat Anda')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#fdfcfb] font-sans text-slate-800 antialiased" x-data="storefront">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:p-4">Lewati ke konten</a>
    @include('partials.public.navbar')
    <main id="main-content">@yield('content')</main>
    @include('partials.public.footer')
    <div x-cloak x-show="notice" x-transition role="status" class="fixed bottom-6 left-1/2 z-50 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 rounded-xl bg-slate-900 px-5 py-4 text-center text-sm text-white shadow-xl" x-text="notice"></div>
    <div x-cloak x-show="cartOpen" @keydown.escape.window="cartOpen = false" class="fixed inset-0 z-50 flex justify-end">
        <button @click="cartOpen = false" class="absolute inset-0 bg-slate-900/40" aria-label="Tutup keranjang"></button>
        <section role="dialog" aria-modal="true" aria-labelledby="cart-title" @keydown.tab="trapFocus($event)" class="relative flex h-full w-full max-w-md flex-col bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between border-b pb-5"><h2 id="cart-title" class="text-xl font-bold">Keranjang demo</h2><button @click="cartOpen = false" aria-label="Tutup keranjang" class="rounded-lg p-2">&times;</button></div>
            <p class="py-4 text-sm text-slate-500">Periksa belanja Anda sebelum checkout.</p>
            <p x-show="!cart.length" class="py-12 text-center text-slate-500">Keranjang Anda masih kosong.</p>
            <div class="flex-1 overflow-y-auto"><template x-for="item in cart" :key="item.id"><div class="flex items-center gap-4 border-b py-4"><div class="flex-1"><p class="font-semibold" x-text="item.name"></p><p class="mt-1 text-sm text-slate-500" x-text="item.quantity + ' × ' + rupiah(item.price)"></p></div><button @click="remove(item.id)" class="text-sm text-rose-700" :aria-label="'Hapus ' + item.name">Hapus</button></div></template></div>
            <div class="flex justify-between border-t pt-5 font-bold"><span>Total sementara</span><span x-text="rupiah(total)"></span></div>
            <form method="POST" action="{{ route('checkout.cart') }}" x-show="cart.length" class="mt-4">
                @csrf
                <input type="hidden" name="cart_payload" :value="JSON.stringify(cart.map(item => ({ id: item.id, quantity: item.quantity })))">
                <button type="submit" class="w-full rounded-xl bg-rose-700 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-rose-800">Lanjut ke checkout</button>
            </form>
        </section>
    </div>
</body>
</html>
