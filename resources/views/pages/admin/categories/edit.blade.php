@extends('layouts.admin')
@section('title', 'Edit Kategori')

@section('content')
<div class="mb-6"><a href="{{ route('admin.categories.index') }}" class="mb-5 inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-brand-700"><span aria-hidden="true">←</span>Kembali ke kategori</a><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Katalog & inventaris</p><h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Edit kategori</h1><p class="mt-2 text-sm text-slate-500">Perbarui nama dan deskripsi kategori produk Anda.</p></div>
<div class="grid items-start gap-6 xl:grid-cols-3">
    <section aria-labelledby="category-form-title" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/20 sm:p-6 xl:col-span-2"><div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-5"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700"><x-admin.icon name="category" /></span><div><h2 id="category-form-title" class="text-sm font-bold text-slate-900">Informasi kategori</h2><p class="mt-1 text-xs text-slate-400">{{ $categoryForForm->name }}</p></div></div>
        @include('pages.admin.categories.partials.form')
        <a href="{{ route('admin.categories.index') }}" class="mt-4 inline-flex rounded-xl border border-slate-200 px-5 py-3 text-xs font-semibold text-slate-500 hover:bg-slate-50">Batal</a>
    </section>
    <aside class="rounded-2xl border border-brand-100 bg-brand-50 p-5"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-brand-700"><x-admin.icon name="product" /></span><h2 class="mt-4 text-sm font-bold text-brand-900">{{ $categoryForForm->products_count }} produk terhubung</h2><p class="mt-3 text-xs leading-6 text-brand-900/70">Perubahan nama kategori akan langsung terlihat pada daftar produk dan dropdown kategori. Produk tetap terhubung ke kategori yang sama.</p></aside>
</div>
@endsection
