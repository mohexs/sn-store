@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Ringkasan toko</p><h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Dashboard</h1><p class="mt-2 text-sm text-slate-500">Pantau katalog dan perkembangan toko Anda dalam satu tempat.</p></div>
    <span class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-500"><x-admin.icon name="calendar" size="h-4 w-4" />{{ now('Asia/Makassar')->format('d/m/Y') }}</span>
</div>

<section aria-labelledby="dashboard-filter-title" class="mb-6 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/20 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 id="dashboard-filter-title" class="text-sm font-bold text-slate-900">Filter periode</h2><p class="mt-1.5 text-xs text-slate-400">Penghasilan menghitung pesanan lunas; jumlah pesanan mencakup semua status.</p></div><a href="{{ route('dashboard') }}" class="text-xs font-semibold text-brand-700 hover:text-brand-900">Hari ini</a></div>
    @if ($errors->any())<div role="alert" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs leading-6 text-rose-700"><ul class="list-disc pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="GET" action="{{ route('dashboard') }}" class="mt-5 flex flex-wrap items-end gap-4">
        <div class="min-w-[150px] flex-1"><x-admin.form-input name="start_date" label="Tanggal mulai" type="date" :value="$filters['start_date']" :required="true" /></div>
        <div class="min-w-[150px] flex-1"><x-admin.form-input name="end_date" label="Tanggal akhir" type="date" :value="$filters['end_date']" :required="true" /></div>
        <button type="submit" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-800">Terapkan filter</button>
    </form>
</section>

<section aria-label="Selamat datang" class="relative mb-6 overflow-hidden rounded-2xl bg-brand-700 px-6 py-7 text-white sm:px-8">
    <div class="pointer-events-none absolute -bottom-28 -right-4 h-80 w-80 rounded-full border-[42px] border-white/5" aria-hidden="true"></div><div class="pointer-events-none absolute -right-12 -top-36 h-80 w-80 rounded-full border border-white/10" aria-hidden="true"></div>
    <div class="relative flex flex-col justify-between gap-5 md:flex-row md:items-center"><div class="max-w-xl"><span class="text-[9px] font-semibold uppercase tracking-[.2em] text-brand-200">Your health partner · Your store workspace</span><h2 class="mt-3 break-words text-xl font-bold tracking-tight sm:text-2xl">Selamat datang, {{ auth()->user()->name }}.</h2><p class="mt-2 text-sm leading-6 text-brand-100">Langkah sehat dimulai dari toko yang terkelola dengan baik.<br class="hidden sm:block">Mari berikan pengalaman terbaik untuk pelanggan Anda.</p></div><a href="{{ route('home') }}" class="inline-flex shrink-0 items-center justify-center gap-5 self-start rounded-xl bg-white px-5 py-3 text-xs font-bold text-brand-800 transition hover:bg-brand-50 md:self-auto">Kunjungi etalase<x-admin.icon name="external" size="h-4 w-4" /></a></div>
</section>

<section aria-label="Statistik toko" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-admin.stat-card label="Total produk" :value="$productCount" description="Produk dalam katalog demo" icon="product" :highlight="true" />
    <x-admin.stat-card label="Kategori produk" :value="$categoryCount" description="Kategori dalam katalog" icon="category" />
    <x-admin.stat-card label="Jumlah pesanan" :value="number_format($orderCount, 0, ',', '.')" :description="\Illuminate\Support\Carbon::parse($filters['start_date'])->format('d/m/Y').' — '.\Illuminate\Support\Carbon::parse($filters['end_date'])->format('d/m/Y')" icon="transaction" />
    <x-admin.stat-card label="Total penghasilan" :value="'Rp'.number_format($revenueMinor / 100, 2, ',', '.')" description="Pesanan lunas pada periode terpilih" icon="income" />
</section>

<div class="mt-6 grid items-stretch gap-6 xl:grid-cols-3">
    <section aria-labelledby="sales-title" class="flex min-w-0 flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/20 sm:p-6 xl:col-span-2">
        <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 id="sales-title" class="text-sm font-bold text-slate-900">Ringkasan penjualan</h2><p class="mt-1.5 text-xs text-slate-400">Data sesuai periode yang dipilih.</p></div><span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-medium text-slate-500">{{ \Illuminate\Support\Carbon::parse($filters['start_date'])->format('d/m/Y') }} — {{ \Illuminate\Support\Carbon::parse($filters['end_date'])->format('d/m/Y') }}</span></div>
        <div class="mt-6 flex min-h-[240px] flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/40 px-5 py-8 text-center">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><x-admin.icon name="report" size="h-6 w-6" /></span><h3 class="mt-4 text-sm font-bold text-slate-700">{{ $orderCount ? 'Ringkasan transaksi periode terpilih' : 'Belum ada pesanan pada periode ini' }}</h3><p class="mt-2 max-w-sm text-xs leading-6 text-slate-400">{{ number_format($orderCount, 0, ',', '.') }} pesanan tercatat dengan penghasilan Rp{{ number_format($revenueMinor / 100, 2, ',', '.') }} dari pesanan lunas.</p>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[10px] text-slate-400"><span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-brand-600"></span>Penghasilan dari pesanan lunas</span><span>Jumlah pesanan mencakup semua status.</span></div>
    </section>
    <section aria-labelledby="category-title" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/20 sm:p-6">
        <div class="flex items-center justify-between gap-3"><h2 id="category-title" class="text-sm font-bold text-slate-900">Komposisi katalog</h2><x-admin.icon name="category" size="h-4 w-4 text-slate-400" /></div><p class="mt-1.5 text-xs text-slate-400">Sebaran produk berdasarkan kategori.</p>
        <div class="mb-6 mt-6 flex items-end gap-2 border-b border-slate-100 pb-5"><span class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $productCount }}</span><span class="pb-1 text-xs text-slate-400">produk / {{ $categoryCount }} kategori</span></div>
        <div class="flex flex-col gap-5">
            @forelse ($categories as $category)
                <div><div class="mb-2 flex justify-between gap-3 text-xs"><h3 class="font-medium text-slate-600">{{ $category['name'] }}</h3><span class="shrink-0 font-semibold text-slate-700">{{ $category['count'] }} <span class="font-normal text-slate-400">produk</span></span></div><div role="meter" aria-label="Persentase produk {{ $category['name'] }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $category['percentage'] }}" class="h-1.5 overflow-hidden rounded-full bg-slate-100"><div @class(['h-full rounded-full', 'bg-brand-600' => $loop->index % 4 === 0, 'bg-brand-400' => $loop->index % 4 === 1, 'bg-orange-300' => $loop->index % 4 === 2, 'bg-slate-400' => $loop->index % 4 === 3]) style="width: {{ $category['percentage'] }}%"></div></div></div>
            @empty
                <p class="py-8 text-center text-xs leading-6 text-slate-400">Belum ada kategori dalam katalog.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="mt-6 grid items-start gap-6 xl:grid-cols-3">
    <section aria-labelledby="catalog-title" class="min-w-0 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/20 xl:col-span-2">
        <div class="flex flex-wrap items-center justify-between gap-3 p-5 sm:p-6"><div><h2 id="catalog-title" class="text-sm font-bold text-slate-900">Produk di etalase</h2><p class="mt-1.5 text-xs text-slate-400">Cuplikan {{ $products->count() }} dari {{ $productCount }} produk katalog demo.</p></div><a href="{{ route('home') }}#produk" class="inline-flex items-center gap-2 text-xs font-semibold text-brand-700 hover:text-brand-900">Lihat etalase<x-admin.icon name="arrow" size="h-3.5 w-3.5" /></a></div>
        <div class="overflow-x-auto"><table class="w-full text-left text-xs"><caption class="sr-only">Cuplikan produk dan harga dari katalog public.</caption><thead class="border-y border-slate-100 bg-slate-50/80 text-[10px] font-medium uppercase tracking-wider text-slate-400"><tr><th scope="col" class="px-5 py-3 font-medium sm:px-6">Produk</th><th scope="col" class="px-4 py-3 font-medium">Kategori</th><th scope="col" class="whitespace-nowrap px-5 py-3 text-right font-medium sm:px-6">Harga</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse ($products as $product)
                <tr class="transition hover:bg-slate-50/60"><td class="px-5 py-3 sm:px-6"><div class="flex items-center gap-3"><div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#f7f7f4] [&>svg]:h-12 [&>svg]:w-12 [&>svg]:drop-shadow-none"><x-public.product-art :type="$product['type']" :color="$product['color']" class="h-12 w-12" /></div><div class="min-w-[130px]"><span class="block font-semibold text-slate-700">{{ $product['name'] }}</span><span class="mt-1 block text-[10px] text-slate-400">{{ $product['unit'] }}</span></div></div></td><td class="px-4 py-3"><span class="whitespace-nowrap rounded-md bg-slate-50 px-2 py-1 text-[10px] text-slate-500">{{ $product['category'] }}</span></td><td class="whitespace-nowrap px-5 py-3 text-right font-bold text-slate-700 sm:px-6">Rp{{ number_format($product['price'], 0, ',', '.') }}</td></tr>
            @empty
                <tr><td colspan="3" class="px-6 py-12 text-center text-slate-400">Belum ada produk di katalog.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="border-t border-slate-100 px-5 py-3.5 text-[10px] leading-5 text-slate-400 sm:px-6">Menggunakan katalog yang sama dengan halaman public. Harga dan ilustrasi merupakan data dummy.</div>
    </section>
    <div class="flex flex-col gap-5">
        <section aria-labelledby="transaction-title" class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6"><div class="flex items-center justify-between"><h2 id="transaction-title" class="text-sm font-bold text-slate-900">Transaksi terbaru</h2><x-admin.icon name="transaction" size="h-4 w-4 text-slate-400" /></div><div class="py-7 text-center"><span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-slate-50 text-slate-400"><x-admin.icon name="transaction" /></span><h3 class="mt-3 text-xs font-semibold text-slate-600">Belum ada data transaksi</h3><p class="mx-auto mt-2 max-w-[220px] text-xs leading-6 text-slate-400">Pesanan pelanggan akan tampil di sini saat pencatatan transaksi tersedia.</p></div></section>
        <section class="rounded-2xl border border-brand-100 bg-brand-50 p-5"><span class="flex items-center gap-2 text-xs font-bold text-brand-900"><x-admin.icon name="shield" size="h-4 w-4 text-brand-700" />Toko yang baik, pelanggan nyaman.</span><p class="mt-3 text-xs leading-6 text-brand-900/60">Informasi produk yang jelas membantu pelanggan memilih kebutuhan kesehatan dengan lebih bijak.</p><a href="{{ route('home') }}" class="mt-4 inline-flex items-center gap-2 text-xs font-bold text-brand-700">Tinjau halaman public<x-admin.icon name="arrow" size="h-3.5 w-3.5" /></a></section>
    </div>
</div>
@endsection
