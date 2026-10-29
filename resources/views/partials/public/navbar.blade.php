<div class="bg-[#a82535] px-4 py-2 text-center text-xs tracking-wide text-white">Kebutuhan kesehatan sehari-hari, dalam satu tempat. <span class="ml-2 hidden text-white/70 sm:inline">Kenali produk, pilih dengan bijak.</span></div>
<header class="sticky top-0 z-30 border-b border-slate-100 bg-white/95 backdrop-blur">
    <nav aria-label="Navigasi utama" class="mx-auto flex max-w-7xl flex-wrap items-center gap-5 px-5 py-5 lg:gap-9 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center" aria-label="{{ $storeName }} beranda">
            @if ($storeLogoUrl)
                <img src="{{ $storeLogoUrl }}" alt="{{ $storeName }}" class="h-10 max-w-48 object-contain">
            @else
                <span class="text-xl font-extrabold tracking-tight">{{ $storeName }}</span>
            @endif
        </a>
        <div class="hidden items-center gap-7 text-sm font-semibold md:flex"><a href="{{ route('home') }}" aria-current="page" class="text-rose-700">Beranda</a><a href="#produk" class="hover:text-rose-700">Produk</a><div x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false" class="relative"><button @click="open = !open" :aria-expanded="open" class="flex items-center gap-2">Kategori <span class="text-xs">⌄</span></button><div x-cloak x-show="open" class="absolute left-0 top-9 w-56 rounded-xl border border-slate-100 bg-white p-2 shadow-xl">@foreach (['Obat-obatan', 'Vitamin & Suplemen', 'Alat Kesehatan', 'Perawatan Diri'] as $category)<a href="#produk" @click="category = @js($category); open = false" class="block rounded-lg px-3 py-2.5 hover:bg-rose-50 hover:text-rose-700">{{ $category }}</a>@endforeach</div></div></div>
        <form @submit.prevent="document.getElementById('produk').scrollIntoView({ behavior: 'smooth' })" role="search" class="order-last flex w-full items-center rounded-full border border-slate-200 bg-slate-50 px-4 md:order-none md:ml-auto md:w-auto md:flex-1 md:max-w-xs"><svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg><input x-model="query" type="search" aria-label="Cari produk" placeholder="Cari kebutuhan sehatmu..." class="w-full border-0 bg-transparent py-2.5 text-xs focus:ring-0"></form>
        <div class="ml-auto flex items-center gap-3 md:ml-0">
            @auth
                <div class="flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 p-1">
                @can('access admin')
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 rounded-full bg-rose-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-rose-800 focus-visible:outline-white">Dashboard <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M7 7h10v10"/></svg></a>
                @endcan
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-white hover:text-rose-700 focus-visible:outline-rose-700">Keluar</button>
                </form>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-full border border-rose-700 px-4 py-2 text-sm font-bold text-rose-700 transition hover:bg-rose-50">Masuk <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M7 7h10v10"/></svg></a>
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-full bg-rose-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-rose-800">Daftar</a>
                </div>
            @endauth
            <button @click="cartOpen = true; $nextTick(() => $el.closest('body').querySelector('[aria-label=\'Tutup keranjang\']').focus())" class="relative p-2" aria-label="Buka keranjang"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l3 13h11l2-9H6M9 21h.01M18 21h.01" stroke-linecap="round"/></svg><span x-show="count" x-cloak x-text="count" class="absolute -right-1 -top-1 rounded-full bg-rose-700 px-1.5 text-[10px] text-white"></span></button>
        </div>
        <div class="flex w-full gap-5 text-sm font-semibold md:hidden"><a href="#produk" @click="category = 'Semua'">Produk</a><a href="#kategori">Kategori</a><a href="#kontak">Kontak</a></div>
    </nav>
</header>
