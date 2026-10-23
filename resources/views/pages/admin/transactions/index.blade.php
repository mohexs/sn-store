@extends('layouts.admin')
@section('title', $stages[$stage])

@section('content')
<div class="mb-6"><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Operasional pesanan</p><h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $stages[$stage] }}</h1><p class="mt-2 text-sm text-slate-500">Kelola pesanan lunas dari pengambilan barang hingga diserahkan ke kurir.</p></div>
<nav aria-label="Tahap transaksi" class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($stages as $key => $label)
        <a href="{{ route('admin.transactions.index', ['stage' => $key]) }}" @if ($stage === $key) aria-current="page" @endif @class(['flex items-center justify-between gap-3 rounded-2xl border p-4 text-sm font-semibold transition', 'border-brand-700 bg-brand-700 text-white' => $stage === $key, 'border-slate-200 bg-white text-slate-600 hover:border-brand-300' => $stage !== $key])><span>{{ $loop->iteration }}. {{ $label }}</span><span class="rounded-lg bg-black/5 px-2 py-1 text-xs">{{ $counts[$key] ?? 0 }}</span></a>
    @endforeach
</nav>
@if (session('success'))<div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
@if ($errors->any())<div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><ul class="list-disc pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="GET" action="{{ route('admin.transactions.index', ['stage' => $stage]) }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-5">
    <div class="min-w-[200px] flex-1"><x-admin.form-input name="q" label="Cari pesanan" :value="$keyword" placeholder="Nomor pesanan atau nama pelanggan" maxlength="100" /></div><button class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-800">Cari</button><a href="{{ route('admin.transactions.index', ['stage' => $stage]) }}" class="rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-600">Reset</a>
</form>
<p class="mb-4 text-xs leading-6 text-slate-500">{{ ['picking' => 'Ambil barang sesuai daftar, lalu konfirmasi untuk mengisi qty yang diproses.', 'processing' => 'Qty merupakan total yang sudah diproses, bukan penambahan. Simpan progres sebagian atau lanjutkan setelah seluruh qty lengkap.', 'packaging' => 'Periksa barang dan kemasan, lalu tandai pesanan siap dikirim.', 'shipping' => 'Isi kurir dan nomor resi saat paket diserahkan. Pesanan terkirim tetap tampil sebagai riwayat.'][$stage] }} Angka pada tahap menunjukkan pesanan yang menunggu diproses.</p>
<div class="space-y-5">
    @forelse ($orders as $order)
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5"><div><h2 class="text-sm font-bold">{{ $order->order_number }}</h2><p class="mt-1 text-xs text-slate-500">{{ $order->customer_name ?: 'Tanpa nama pelanggan' }} · {{ $order->sale_date->format('d/m/Y') }}</p></div><span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">{{ $order->fulfillment_status === 'shipped' ? 'Sudah dikirim' : $stages[$order->fulfillment_status] }}</span></div>
            <form method="POST" action="{{ route('admin.transactions.update', $order) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs text-slate-500"><tr><th scope="col" class="px-5 py-3">Barang / SKU</th><th scope="col" class="px-5 py-3">Qty pesanan</th><th scope="col" class="px-5 py-3">Qty proses</th></tr></thead><tbody class="divide-y divide-slate-100">
                    @forelse ($order->items as $item)
                        <tr><td class="px-5 py-4"><span class="font-semibold">{{ $item->product_name }}</span><span class="mt-1 block text-xs text-slate-400">{{ $item->product_sku }}</span></td><td class="px-5 py-4">{{ $item->quantity }}</td><td class="px-5 py-4">
                            @if ($stage === 'processing')
                                <label class="sr-only" for="qty-{{ $item->id }}">Qty proses {{ $item->product_name }} untuk {{ $order->order_number }}</label><input id="qty-{{ $item->id }}" name="quantities[{{ $item->id }}]" type="number" min="0" max="{{ $item->quantity }}" step="1" required value="{{ (string) old('order_id') === (string) $order->id ? old('quantities.'.$item->id, $item->processed_quantity) : $item->processed_quantity }}" class="w-24 rounded-lg border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                            @else {{ $item->processed_quantity }} @endif
                        </td></tr>
                    @empty <tr><td colspan="3" class="px-5 py-6 text-slate-500">Belum ada barang pada pesanan ini.</td></tr> @endforelse
                </tbody></table></div>
                <div class="flex flex-wrap items-end justify-end gap-3 border-t border-slate-100 p-5">
                    @if ($order->fulfillment_status === 'shipped')
                        <p class="w-full text-sm text-slate-600">{{ $order->shipping_carrier }} · Resi: <span class="font-semibold">{{ $order->tracking_number }}</span><span class="mt-2 block text-xs text-slate-400">Dikirim {{ $order->shipped_at?->timezone('Asia/Makassar')->format('d/m/Y H:i') }} WITA</span></p>
                    @else
                        @if ($stage === 'shipping')
                            <div class="min-w-[180px] flex-1"><label for="carrier-{{ $order->id }}" class="mb-2 block text-xs font-semibold text-slate-600">Kurir <span aria-hidden="true">*</span></label><input id="carrier-{{ $order->id }}" name="shipping_carrier" required maxlength="100" placeholder="Contoh: JNE" value="{{ (string) old('order_id') === (string) $order->id ? old('shipping_carrier') : '' }}" class="w-full rounded-xl border-slate-200 py-3 text-sm"></div>
                            <div class="min-w-[180px] flex-1"><label for="tracking-{{ $order->id }}" class="mb-2 block text-xs font-semibold text-slate-600">Nomor resi <span aria-hidden="true">*</span></label><input id="tracking-{{ $order->id }}" name="tracking_number" required maxlength="100" value="{{ (string) old('order_id') === (string) $order->id ? old('tracking_number') : '' }}" class="w-full rounded-xl border-slate-200 py-3 text-sm"></div>
                        @endif
                        @if ($stage === 'processing')<button name="action" value="save" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Simpan progres qty</button>@endif
                        <button name="action" value="{{ ['picking' => 'pick', 'processing' => 'process', 'packaging' => 'pack', 'shipping' => 'ship'][$stage] }}" @disabled($order->items->isEmpty()) class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-800 disabled:opacity-50">{{ ['picking' => 'Konfirmasi picking', 'processing' => 'Lanjut ke packaging', 'packaging' => 'Selesai packaging', 'shipping' => 'Konfirmasi pengiriman'][$stage] }}</button>
                    @endif
                </div>
            </form>
        </article>
    @empty
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center"><h2 class="text-sm font-bold text-slate-700">Belum ada pesanan pada tahap ini</h2><p class="mt-2 text-xs leading-6 text-slate-500">Pesanan lunas akan muncul sesuai tahap prosesnya. Coba ubah pencarian jika diperlukan.</p></div>
    @endforelse
</div>
<div class="mt-6">{{ $orders->links() }}</div>
@endsection
