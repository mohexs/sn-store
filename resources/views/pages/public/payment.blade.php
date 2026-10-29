@extends('layouts.public')
@section('title', 'Pembayaran — '.$storeName)

@section('content')
<section class="mx-auto max-w-4xl px-5 py-10 lg:px-8 lg:py-14">
    <div class="mb-8"><p class="text-[10px] font-bold uppercase tracking-[.2em] text-rose-700">Pesanan berhasil dibuat</p><h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Pembayaran</h1><p class="mt-2 text-sm text-slate-500">Selesaikan pembayaran manual untuk memproses pesanan Anda.</p></div>
    <div class="grid items-start gap-6 md:grid-cols-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 md:col-span-3">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5"><div><p class="text-xs text-slate-400">Nomor pesanan</p><p class="mt-1 font-mono text-lg font-bold text-slate-900">{{ $order->order_number }}</p><p class="mt-1 text-xs text-slate-500">Atas nama {{ $order->customer_name }}</p></div><span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800">Menunggu pembayaran</span></div>
            <div class="mt-5 flex flex-col gap-4">
                <div class="flex justify-between gap-4 text-sm"><span class="text-slate-500">Metode</span><span class="font-semibold text-slate-700">Transfer bank manual</span></div>
                <div class="flex justify-between gap-4 text-sm"><span class="text-slate-500">Total tagihan</span><span class="font-extrabold text-rose-700">Rp{{ number_format($order->total_minor / 100, 0, ',', '.') }}</span></div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs leading-6 text-amber-900"><p class="font-bold">Instruksi pembayaran</p><p class="mt-1">Silakan hubungi admin toko untuk mendapatkan detail rekening resmi sebelum melakukan transfer. Jangan transfer ke rekening yang belum dikonfirmasi.</p><p class="mt-2">Status pesanan akan diperbarui setelah admin memeriksa pembayaran Anda.</p></div>
            </div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 md:col-span-2">
            <h2 class="text-sm font-bold text-slate-900">Ringkasan pesanan</h2>
            <div class="mt-4 flex flex-col gap-4">
                @foreach ($order->items as $item)
                    <div class="flex justify-between gap-3 text-xs"><div><p class="font-semibold text-slate-700">{{ $item->product_name }}</p><p class="mt-1 text-slate-400">{{ $item->quantity }} × Rp{{ number_format($item->unit_price_minor / 100, 0, ',', '.') }}</p></div><span class="shrink-0 font-semibold text-slate-700">Rp{{ number_format($item->line_total_minor / 100, 0, ',', '.') }}</span></div>
                @endforeach
            </div>
            <dl class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-4 text-xs">
                <div class="flex justify-between gap-3 text-slate-500"><dt>Subtotal</dt><dd>Rp{{ number_format($order->subtotal_minor / 100, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-3 text-slate-500"><dt>Diskon{{ $order->promotion ? ' · '.$order->promotion->name : '' }}</dt><dd class="text-emerald-700">−Rp{{ number_format($order->discount_minor / 100, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-3 border-t border-slate-100 pt-3 text-sm font-extrabold text-slate-900"><dt>Total</dt><dd>Rp{{ number_format($order->total_minor / 100, 0, ',', '.') }}</dd></div>
            </dl>
        </section>
    </div>
    <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 hover:border-rose-200 hover:text-rose-700">Kembali ke beranda</a>
</section>
@endsection
