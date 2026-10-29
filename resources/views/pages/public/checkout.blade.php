@extends('layouts.public')
@section('title', 'Checkout — '.$storeName)

@section('content')
<section class="mx-auto max-w-6xl px-5 py-10 lg:px-8 lg:py-14">
    <div class="mb-8"><a href="{{ route('home') }}#produk" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-rose-700"><span aria-hidden="true">←</span>Kembali belanja</a><p class="mt-5 text-[10px] font-bold uppercase tracking-[.2em] text-rose-700">Satu langkah lagi</p><h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Checkout</h1><p class="mt-2 text-sm text-slate-500">Tinjau detail belanja dan diskon sebelum melanjutkan ke pembayaran.</p></div>
    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800"><p class="font-bold">Checkout belum dapat dilanjutkan.</p><ul class="mt-2 list-disc pl-5 text-xs leading-6">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if ($summary['lines']->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm"><span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-700" aria-hidden="true">🛒</span><h2 class="mt-5 text-lg font-bold text-slate-800">Keranjang Anda masih kosong</h2><p class="mt-2 text-sm text-slate-500">Pilih produk terlebih dahulu sebelum checkout.</p><a href="{{ route('home') }}#produk" class="mt-6 inline-flex rounded-xl bg-rose-700 px-5 py-3 text-sm font-bold text-white hover:bg-rose-800">Jelajahi produk</a></div>
    @else
        <div class="grid items-start gap-6 lg:grid-cols-5">
            <section aria-labelledby="checkout-items-title" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-3">
                <div class="border-b border-slate-100 px-5 py-5 sm:px-6"><h2 id="checkout-items-title" class="text-sm font-bold text-slate-900">Detail belanja</h2><p class="mt-1 text-xs text-slate-400">{{ $summary['lines']->sum('quantity') }} barang di keranjang</p></div>
                <div class="divide-y divide-slate-100 px-5 sm:px-6">
                    @foreach ($summary['lines'] as $line)
                        <article class="flex flex-wrap items-center justify-between gap-3 py-5">
                            <div class="min-w-[180px] flex-1"><h3 class="text-sm font-semibold text-slate-800">{{ $line['name'] }}</h3><p class="mt-1 text-xs text-slate-400">{{ $line['quantity'] }} × Rp{{ number_format($line['price'], 0, ',', '.') }} · SKU {{ $line['sku'] }}</p></div>
                            <div class="text-right"><p class="text-sm font-bold text-slate-800">Rp{{ number_format(($line['line_total_minor'] - $line['discount_minor']) / 100, 0, ',', '.') }}</p>@if ($line['discount_minor'] > 0)<p class="mt-1 text-[11px] text-emerald-700">Diskon −Rp{{ number_format($line['discount_minor'] / 100, 0, ',', '.') }}</p>@endif</div>
                        </article>
                    @endforeach
                </div>
            </section>
            <div class="flex flex-col gap-6 lg:col-span-2">
                <section aria-labelledby="checkout-total-title" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 id="checkout-total-title" class="text-sm font-bold text-slate-900">Ringkasan belanja</h2>
                    <dl class="mt-5 flex flex-col gap-4 text-sm">
                        <div class="flex justify-between gap-4 text-slate-500"><dt>Subtotal</dt><dd class="font-semibold text-slate-700">Rp{{ number_format($summary['subtotal_minor'] / 100, 0, ',', '.') }}</dd></div>
                        <div class="flex justify-between gap-4 text-slate-500"><dt class="min-w-0">Diskon @if ($summary['promotion'])<span class="block truncate text-[11px] text-emerald-700">{{ $summary['promotion']->name }}</span>@endif</dt><dd class="shrink-0 font-semibold text-emerald-700">−Rp{{ number_format($summary['discount_minor'] / 100, 0, ',', '.') }}</dd></div>
                        <div class="flex justify-between gap-4 border-t border-slate-100 pt-4 text-base font-extrabold text-slate-900"><dt>Total belanja</dt><dd>Rp{{ number_format($summary['total_minor'] / 100, 0, ',', '.') }}</dd></div>
                    </dl>
                    @if ($summary['promotion'])
                        <p class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-xs leading-5 text-emerald-800">Promosi aktif <strong>{{ $summary['promotion']->name }}</strong> diterapkan otomatis sebagai diskon terbaik yang tersedia.</p>
                    @else
                        <p class="mt-5 rounded-xl bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-500">Belum ada promosi aktif untuk saat ini.</p>
                    @endif
                    <p class="mt-4 text-[11px] leading-5 text-slate-400">Katalog dan harga pada halaman ini merupakan data demo. Biaya pengiriman belum termasuk.</p>
                </section>
                <form method="POST" action="{{ route('checkout.order') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    @csrf
                    <h2 class="text-sm font-bold text-slate-900">Informasi pembayaran</h2>
                    <div class="mt-5 flex flex-col gap-5">
                        <label for="customer_name" class="text-xs font-semibold text-slate-600">Nama penerima<input id="customer_name" name="customer_name" type="text" required maxlength="255" value="{{ old('customer_name', auth()->user()?->name) }}" autocomplete="name" placeholder="Nama lengkap" class="mt-2 w-full rounded-xl border-slate-200 px-3.5 py-3 text-sm font-normal text-slate-700 focus:border-rose-500 focus:ring-rose-500"></label>
                        <fieldset><legend class="text-xs font-semibold text-slate-600">Metode pembayaran</legend><label class="mt-3 flex cursor-pointer items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4"><input type="radio" name="payment_method" value="bank_transfer" checked class="mt-0.5 border-slate-300 text-rose-700 focus:ring-rose-500"><span><span class="block text-sm font-semibold text-slate-800">Transfer bank manual</span><span class="mt-1 block text-xs leading-5 text-slate-500">Pesanan menunggu pembayaran dan konfirmasi admin.</span></span></label></fieldset>
                    </div>
                    <button type="submit" class="mt-6 w-full rounded-xl bg-rose-700 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-rose-800">Buat pesanan & lanjut bayar</button>
                    <p class="mt-3 text-center text-[10px] leading-5 text-slate-400">Pesanan belum dianggap lunas sebelum pembayaran dikonfirmasi admin.</p>
                </form>
            </div>
        </div>
    @endif
</section>
@endsection
