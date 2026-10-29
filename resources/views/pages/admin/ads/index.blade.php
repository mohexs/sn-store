@extends('layouts.admin')
@section('title', 'Ads')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Konten etalase</p><h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Ads & banner hero</h1><p class="mt-2 text-sm text-slate-500">Kelola foto dan keterangan banner utama yang tampil di halaman publik.</p></div>
    <a href="{{ route('admin.ads.create') }}" class="inline-flex items-center gap-3 rounded-xl bg-brand-700 px-5 py-3 text-xs font-bold text-white transition hover:bg-brand-800"><span aria-hidden="true" class="text-lg leading-none">+</span>Tambah banner</a>
</div>
@if (session('status'))<div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('status') }}</div>@endif
<div class="mb-6 rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4 text-xs leading-6 text-sky-900"><p class="font-bold">Panduan gambar banner</p><p class="mt-1">Format JPG, JPEG, PNG, atau WebP · Maksimal 2 MB · Maksimal 4096 × 4096 piksel. Ukuran yang disarankan 1200 × 900 piksel agar banner tetap tajam. Gambar tidak diubah ukurannya otomatis; pratinjau akan menyesuaikan area hero.</p></div>
<section aria-labelledby="ads-list-title" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/20">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6"><div><h2 id="ads-list-title" class="text-sm font-bold text-slate-900">Daftar banner</h2><p class="mt-1 text-xs text-slate-400">Banner aktif ditampilkan berurutan di hero halaman publik.</p></div><span class="rounded-full bg-brand-50 px-3 py-1 text-[10px] font-semibold text-brand-700">{{ $ads->total() }} banner</span></div>
    @if ($ads->isEmpty())
        <div class="px-6 py-16 text-center"><span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><x-admin.icon name="ads" size="h-8 w-8" /></span><h3 class="mt-5 text-base font-bold text-slate-800">Belum ada banner khusus</h3><p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-400">Halaman publik saat ini menampilkan banner bawaan. Tambahkan banner untuk menggantinya.</p><a href="{{ route('admin.ads.create') }}" class="mt-6 inline-flex items-center gap-3 rounded-xl bg-brand-700 px-5 py-3 text-xs font-bold text-white hover:bg-brand-800">Tambah banner hero<x-admin.icon name="arrow" size="h-4 w-4" /></a></div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach ($ads as $ad)
                <article class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:p-6">
                    <img src="{{ Storage::disk('public')->url($ad->image_path) }}" alt="Banner {{ $ad->title }}" width="192" height="128" loading="lazy" class="h-32 w-full rounded-xl border border-slate-100 bg-slate-50 object-cover sm:w-48">
                    <div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-2"><span class="text-[10px] font-bold uppercase tracking-wider text-brand-700">{{ $ad->eyebrow }}</span><span @class(['rounded-full px-2.5 py-1 text-[10px] font-semibold', 'bg-emerald-50 text-emerald-700' => $ad->is_active, 'bg-slate-100 text-slate-500' => ! $ad->is_active])>{{ $ad->is_active ? 'Aktif' : 'Nonaktif' }}</span></div><h3 class="mt-2 text-lg font-bold text-slate-900">{{ $ad->title }} <span class="text-brand-700">{{ $ad->accent }}</span></h3><p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $ad->copy }}</p><p class="mt-2 text-[10px] text-slate-400">Urutan {{ $ad->sort_order }}</p></div>
                    <div class="flex shrink-0 items-center gap-2"><a href="{{ route('admin.ads.edit', $ad) }}" aria-label="Edit banner {{ $ad->title }}" class="rounded-lg border border-brand-100 px-3 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-50">Edit</a><form method="POST" action="{{ route('admin.ads.destroy', $ad) }}" data-confirm-delete data-confirm-title="Hapus banner ini?" data-confirm-text="Banner '{{ $ad->title }}' akan dihapus permanen.">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-rose-100 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Hapus</button></form></div>
                </article>
            @endforeach
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $ads->links() }}</div>
    @endif
</section>
@endsection
