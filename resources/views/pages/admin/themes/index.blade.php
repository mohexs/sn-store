@extends('layouts.admin')
@section('title', 'Tema')

@section('content')
<div x-data="adminThemePicker">
    @if (session('status'))<div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('status') }}</div>@endif
    <section aria-labelledby="store-logo-heading" class="mb-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/20 sm:p-6">
        <div class="mb-5"><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Identitas toko</p><h2 id="store-logo-heading" class="text-lg font-extrabold text-slate-900">Gambar logo dan nama toko</h2><p class="mt-2 text-sm leading-6 text-slate-500">Unggah satu gambar yang sudah menggabungkan desain logo dan tulisan nama toko. Gambar ini akan tampil sebagai satu kesatuan di etalase dan panel admin.</p></div>
        <form method="POST" action="{{ route('admin.themes.logo.update') }}" enctype="multipart/form-data" class="flex flex-col gap-5 sm:flex-row sm:items-end">
            @csrf
            <div class="flex min-h-24 items-center gap-4 sm:min-w-64">
                @if ($storeLogoUrl)
                    <img src="{{ $storeLogoUrl }}" alt="Gambar identitas toko saat ini" class="h-20 max-w-56 rounded-xl border border-slate-200 bg-white object-contain p-2">
                @else
                    <span class="text-xs font-medium text-slate-500">Belum ada gambar identitas toko</span>
                @endif
            </div>
            <div class="flex-1">
                <label for="logo" class="mb-2 block text-xs font-semibold text-slate-700">Upload gambar logo dan nama toko</label>
                <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" required aria-invalid="{{ $errors->has('logo') ? 'true' : 'false' }}" aria-describedby="logo-hint" class="block w-full text-xs text-slate-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2.5 file:text-xs file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                <p id="logo-hint" class="mt-2 text-xs leading-5 text-slate-400">Pastikan satu gambar sudah memuat logo beserta tulisan nama toko. Disarankan rasio landscape 3:1 (misalnya 600 × 200 piksel). JPG, PNG, atau WebP; maksimal 2 MB dan dimensi 4096 × 4096 piksel.</p>
                <x-input-error id="logo-error" :messages="$errors->get('logo')" class="mt-2 text-xs text-rose-700" />
            </div>
            <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-brand-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-800">Simpan gambar</button>
        </form>
    </section>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4"><div><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Personalisasi workspace</p><h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Warna yang terasa seperti Anda.</h1><p class="mt-2 text-sm text-slate-500">Pilih tema favorit. Satu klik, seluruh tampilan admin mengikuti.</p></div><button @click="choose('rose')" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs font-semibold text-slate-600 hover:bg-slate-50">Kembalikan tema awal</button></div>
    <section aria-label="Pratinjau tema aktif" class="relative mb-7 overflow-hidden rounded-2xl bg-brand-700 px-6 py-8 text-white sm:px-8"><div class="absolute -right-12 -top-28 h-80 w-80 rounded-full border-[45px] border-white/10" aria-hidden="true"></div><div class="relative flex flex-col justify-between gap-6 sm:flex-row sm:items-center"><div><span class="rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-[10px] font-semibold tracking-wide">Workspace, dengan sentuhan Anda</span><h2 class="mt-5 text-2xl font-bold">Satu warna, suasana baru.</h2><p class="mt-2 max-w-lg text-sm leading-7 text-white/75">Aksen tombol, sidebar, banner, dan form berubah bersama. Tetap rapi dan konsisten di setiap halaman admin.</p></div><span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl border border-white/20 bg-white/10"><x-admin.icon name="theme" size="h-10 w-10" /></span></div></section>
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3"><h2 class="text-sm font-bold text-slate-800">Pilih palet warna Anda</h2><p role="status" aria-live="polite" class="text-xs text-slate-500" x-text="message">Pilihan disimpan pada browser ini untuk akun Anda.</p></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach (config('admin.themes') as $id => $theme)
            <button type="button" @click="choose(@js($id))" :aria-pressed="selected === @js($id)" :class="selected === @js($id) ? 'border-brand-500 ring-2 ring-brand-100' : 'border-slate-200 hover:border-slate-300'" class="group overflow-hidden rounded-2xl border bg-white p-4 text-left shadow-sm shadow-slate-200/20 transition">
                <span class="block overflow-hidden rounded-xl border border-slate-100 bg-slate-50 p-3" style="--swatch: {{ $theme['color'] }}"><span class="flex gap-2"><span class="flex w-10 shrink-0 flex-col gap-1 rounded-md bg-white p-1.5"><span class="mb-2 h-3 w-3 rounded" style="background: var(--swatch)"></span><span class="h-1.5 w-full rounded bg-slate-100"></span><span class="h-1.5 w-full rounded" style="background: var(--swatch); opacity: .4"></span><span class="h-1.5 w-full rounded bg-slate-100"></span></span><span class="block flex-1"><span class="block h-9 rounded-md" style="background: var(--swatch)"></span><span class="mt-2 grid grid-cols-3 gap-2"><span class="h-8 rounded-md bg-white"></span><span class="h-8 rounded-md bg-white"></span><span class="h-8 rounded-md bg-white"></span></span></span></span></span>
                <span class="mt-4 flex items-center justify-between gap-3"><span class="flex items-center gap-2 text-sm font-bold text-slate-700"><span class="h-3 w-3 rounded-full" style="background: {{ $theme['color'] }}" aria-hidden="true"></span>{{ $theme['name'] }}</span><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border" :class="selected === @js($id) ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 text-transparent'" aria-hidden="true">✓</span></span><span class="mt-1.5 block text-xs leading-6 text-slate-400">{{ $theme['description'] }}</span>
            </button>
        @endforeach
    </div>
    <p class="mt-6 text-xs leading-6 text-slate-400">Tema berlaku untuk halaman admin. Halaman publik tetap menggunakan identitas warna Apotek Aja. Pilihan diingat pada browser dan perangkat ini.</p>
</div>
@endsection
