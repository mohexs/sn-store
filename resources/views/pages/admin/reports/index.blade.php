@extends('layouts.admin')
@section('title', 'Laporan Penjualan')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4"><div><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Analisis & performa</p><h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Laporan penjualan</h1><p class="mt-2 text-sm text-slate-500">Telusuri penjualan harian, performa produk, dan detail setiap pesanan.</p></div><span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-2 text-[10px] font-semibold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Pesanan lunas</span></div>
<nav aria-label="Jenis laporan" class="mb-6 flex flex-wrap gap-2 rounded-2xl border border-slate-200/80 bg-white p-2">
    @foreach (['daily' => ['label' => 'Penjualan Harian', 'icon' => 'calendar'], 'products' => ['label' => 'Penjualan per Produk', 'icon' => 'product'], 'orders' => ['label' => 'Detail Penjualan', 'icon' => 'transaction']] as $type => $tab)
        <a href="{{ route('admin.reports.index', ['type' => $type, 'start_date' => $filters['start_date'], 'end_date' => $filters['end_date']]) }}" @if ($filters['type'] === $type) aria-current="page" @endif @class(['flex flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-xl px-4 py-3 text-xs font-semibold transition', 'bg-brand-700 text-white shadow-sm' => $filters['type'] === $type, 'text-slate-500 hover:bg-slate-50' => $filters['type'] !== $type])><x-admin.icon :name="$tab['icon']" size="h-4 w-4" />{{ $tab['label'] }}</a>
    @endforeach
</nav>
<section aria-labelledby="filter-title" class="mb-6 rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6"><h2 id="filter-title" class="text-sm font-bold text-slate-900">Filter laporan</h2><p class="mt-1.5 text-xs text-slate-400">Tanggal awal dan akhir termasuk dalam perhitungan. Periode awal: bulan berjalan.</p>
    @if ($errors->any())<div role="alert" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs leading-6 text-rose-700"><ul class="list-disc pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="GET" action="{{ route('admin.reports.index') }}" class="mt-5 flex flex-wrap items-end gap-4"><input type="hidden" name="type" value="{{ $filters['type'] }}">
        <div class="min-w-[150px] flex-1"><x-admin.form-input name="start_date" label="Tanggal mulai" type="date" :value="$filters['start_date']" :required="true" /></div><div class="min-w-[150px] flex-1"><x-admin.form-input name="end_date" label="Tanggal akhir" type="date" :value="$filters['end_date']" :required="true" /></div>
        @if ($filters['type'] !== 'daily')<div class="min-w-[200px] flex-[2]"><x-admin.form-input name="q" :label="$filters['type'] === 'products' ? 'Nama produk / SKU' : 'Nomor pesanan / pelanggan'" :value="$filters['q'] ?? ''" maxlength="100" placeholder="Ketik untuk mencari..." /></div>@endif
        @if ($filters['type'] === 'products')<div class="min-w-[170px] flex-1"><x-admin.form-field name="sort" label="Urutkan"><select id="sort" name="sort" class="w-full rounded-xl border-slate-200 px-3 py-3 text-sm text-slate-700 focus:border-brand-500 focus:ring-brand-500">@foreach (['revenue' => 'Penjualan tertinggi', 'quantity' => 'Jumlah terjual terbanyak', 'name' => 'Nama produk A–Z'] as $value => $label)<option value="{{ $value }}" @selected(($filters['sort'] ?? 'revenue') === $value)>{{ $label }}</option>@endforeach</select></x-admin.form-field></div>@endif
        <button type="submit" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-800">Terapkan</button><a href="{{ route('admin.reports.index', ['type' => $filters['type']]) }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-500 hover:bg-slate-50">Reset</a>
    </form>
</section>
<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-admin.stat-card label="Penjualan bersih" :value="'Rp'.number_format($summary->net_minor / 100, 2, ',', '.')" description="Setelah diskon produk" icon="income" :highlight="true" />
    <x-admin.stat-card label="Pesanan lunas" :value="number_format($summary->orders_count, 0, ',', '.')" description="Pesanan unik sesuai filter" icon="transaction" />
    <x-admin.stat-card label="Jumlah produk terjual" :value="number_format($summary->quantity, 0, ',', '.')" description="Total unit sesuai filter" icon="product" />
    <x-admin.stat-card label="Total diskon" :value="'Rp'.number_format($summary->discount_minor / 100, 2, ',', '.')" description="Diskon seluruh produk terpilih" icon="promotion" />
</div>
<section aria-labelledby="report-table-title" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/20">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:p-6"><div><h2 id="report-table-title" class="text-sm font-bold text-slate-900">{{ ['daily' => 'Rekap penjualan harian', 'products' => 'Rekap penjualan per produk', 'orders' => 'Detail penjualan per pesanan'][$filters['type']] }}</h2><p class="mt-1.5 text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($filters['start_date'])->format('d/m/Y') }} — {{ \Illuminate\Support\Carbon::parse($filters['end_date'])->format('d/m/Y') }}</p></div><span class="rounded-full bg-brand-50 px-3 py-1 text-[10px] font-semibold text-brand-700">{{ $rows->total() }} {{ ['daily' => 'hari penjualan', 'products' => 'baris produk', 'orders' => 'pesanan'][$filters['type']] }}</span></div>
    @if ($rows->isEmpty())<div class="px-6 py-16 text-center"><span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><x-admin.icon name="report" size="h-7 w-7" /></span><h3 class="mt-4 text-sm font-bold text-slate-700">Belum ada penjualan sesuai filter</h3><p class="mx-auto mt-2 max-w-md text-xs leading-6 text-slate-400">Coba rentang tanggal atau kata pencarian lain. Laporan akan terisi saat pesanan lunas beserta detail produknya sudah tercatat.</p></div>
    @else
        @include('pages.admin.reports.partials.'.$filters['type'])
        <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $rows->links() }}</div>
    @endif
</section>
<p class="mt-5 text-xs leading-6 text-slate-400">Ringkasan mencakup seluruh hasil filter, termasuk halaman tabel lainnya. Penjualan bersih adalah harga produk dikali jumlah, dikurangi diskon produk; belum termasuk ongkir atau pajak. Pesanan yang belum lunas, dibatalkan, atau direfund tidak dihitung.</p>
@endsection
