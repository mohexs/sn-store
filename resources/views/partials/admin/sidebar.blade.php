<aside id="admin-sidebar" aria-label="Navigasi admin" :inert="!desktop && !sidebarOpen" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" @keydown.tab="trapSidebarFocus($event)" class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-200/80 bg-white transition-transform duration-200 lg:translate-x-0">
    <div class="flex h-20 shrink-0 items-center justify-between border-b border-slate-100 px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5" aria-label="SN Store dashboard"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-700 text-xl font-black tracking-tighter text-white">sn<span class="text-brand-200">+</span></span><span class="text-xl font-extrabold tracking-tight">sn<span class="text-brand-700">-store</span><span class="mt-0.5 block text-[9px] font-semibold uppercase tracking-[.22em] text-slate-400">Admin workspace</span></span></a>
        <button x-ref="sidebarClose" @click="closeSidebar()" aria-label="Tutup menu" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-50 lg:hidden"><x-admin.icon name="close" size="h-4 w-4" /></button>
    </div>
    <div class="flex-1 overflow-y-auto px-4 py-6">
        <p class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[.18em] text-slate-400">Workspace</p>
        <nav class="flex flex-col gap-1.5" aria-label="Menu utama">
            @foreach ([
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard', 'pattern' => 'dashboard'],
                ['label' => 'Kategori', 'icon' => 'category', 'route' => 'admin.categories.index', 'pattern' => 'admin.categories.*'],
                ['label' => 'Produk', 'icon' => 'product', 'route' => 'admin.products.index', 'pattern' => 'admin.products.*'],
                ['label' => 'Promosi', 'icon' => 'promotion', 'route' => null],
                ['label' => 'Transaksi', 'icon' => 'transaction', 'route' => null],
                ['label' => 'Penghasilan', 'icon' => 'income', 'route' => null],
                ['label' => 'Laporan', 'icon' => 'report', 'route' => null],
                ['label' => 'User', 'icon' => 'users', 'route' => null],
                ['label' => 'Tema', 'icon' => 'theme', 'route' => 'admin.themes.index', 'pattern' => 'admin.themes.*'],
            ] as $item)
                @if ($item['route'])
                    <a href="{{ route($item['route']) }}" @if (request()->routeIs($item['pattern'])) aria-current="page" @endif @class(['flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition', 'bg-brand-50 text-brand-700' => request()->routeIs($item['pattern']), 'text-slate-600 hover:bg-slate-50' => ! request()->routeIs($item['pattern'])])><x-admin.icon :name="$item['icon']" /><span class="flex-1">{{ $item['label'] }}</span>@if (request()->routeIs($item['pattern']))<span class="h-1.5 w-1.5 rounded-full bg-brand-600" aria-hidden="true"></span>@endif</a>
                @else
                    <button type="button" disabled title="Halaman {{ $item['label'] }} belum tersedia" class="flex w-full cursor-not-allowed items-center gap-3 rounded-xl px-3 py-3 text-left text-sm text-slate-400"><x-admin.icon :name="$item['icon']" /><span class="flex-1">{{ $item['label'] }}</span><span class="rounded-md bg-slate-50 px-1.5 py-0.5 text-[9px] font-medium text-slate-400">Segera</span></button>
                @endif
            @endforeach
        </nav>
        <p class="mt-7 border-t border-slate-100 px-3 pt-6 text-[10px] font-semibold uppercase tracking-[.18em] text-slate-400">Toko Anda</p>
        <a href="{{ route('home') }}" class="mt-2 flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50"><x-admin.icon name="external" /><span>Lihat etalase</span></a>
    </div>
    <div class="shrink-0 p-4">
        <div class="relative overflow-hidden rounded-2xl bg-brand-50 p-4"><div class="absolute -right-6 -top-6 h-20 w-20 rounded-full border-[15px] border-brand-100" aria-hidden="true"></div><span class="relative flex h-8 w-8 items-center justify-center rounded-lg bg-white text-brand-700"><x-admin.icon name="shield" size="h-4 w-4" /></span><p class="relative mt-3 text-xs font-bold text-brand-950">Kelola dengan kepedulian.</p><p class="relative mt-1.5 text-[11px] leading-5 text-brand-900/60">Setiap detail toko, untuk pengalaman sehat yang lebih baik.</p></div>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf<button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 hover:bg-brand-50 hover:text-brand-700"><x-admin.icon name="logout" />Keluar</button></form>
    </div>
</aside>
