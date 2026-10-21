@extends('layouts.admin')
@section('title', 'Kategori')

@section('content')
<div class="mb-6"><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Katalog & inventaris</p><h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Kategori produk</h1><p class="mt-2 text-sm text-slate-500">Kelompokkan produk agar katalog toko lebih mudah dikelola.</p></div>
@if (session('status'))<div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('status') }}</div>@endif
<div class="grid items-start gap-6 xl:grid-cols-3">
    <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/20 xl:col-span-2" aria-labelledby="categories-title">
        <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-5 sm:p-6"><div><h2 id="categories-title" class="text-sm font-bold text-slate-900">Daftar kategori</h2><p class="mt-1.5 text-xs text-slate-400">Terhubung langsung dengan pilihan kategori produk.</p></div><span class="shrink-0 rounded-full bg-brand-50 px-3 py-1 text-[10px] font-semibold text-brand-700">{{ $categories->total() }} kategori</span></div>
        <div class="overflow-x-auto"><table class="w-full text-left text-xs"><caption class="sr-only">Kategori dan jumlah produk yang terhubung.</caption><thead class="border-b border-slate-100 bg-slate-50 text-[10px] uppercase tracking-wider text-slate-400"><tr><th scope="col" class="px-6 py-3 font-medium">Kategori</th><th scope="col" class="px-4 py-3 font-medium">Deskripsi</th><th scope="col" class="px-6 py-3 text-right font-medium">Produk</th><th scope="col" class="px-6 py-3 text-right font-medium">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse ($categories as $category)
                <tr class="hover:bg-slate-50/60"><td class="px-6 py-4"><div class="flex items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-admin.icon name="category" size="h-4 w-4" /></span><span class="min-w-[120px] font-semibold text-slate-700">{{ $category->name }}</span></div></td><td class="min-w-[160px] max-w-xs px-4 py-4 text-slate-500"><p class="line-clamp-2">{{ $category->description ?? 'Belum ada deskripsi.' }}</p></td><td class="px-6 py-4 text-right font-bold text-slate-700">{{ $category->products_count }}</td><td class="px-6 py-4 text-right"><a href="{{ route('admin.categories.edit', $category) }}" aria-label="Edit {{ $category->name }}" class="inline-flex rounded-lg border border-brand-100 px-3 py-2 font-semibold text-brand-700 hover:bg-brand-50">Edit</a></td></tr>
            @empty
                <tr><td colspan="4" class="px-6 py-14 text-center text-slate-400">Belum ada kategori. Tambahkan kategori pertama melalui form di samping.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $categories->links() }}</div>
    </section>
    <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/20 sm:p-6" aria-labelledby="category-form-title"><span class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700"><x-admin.icon name="category" /></span><h2 id="category-form-title" class="text-sm font-bold text-slate-900">Tambah kategori baru</h2><p class="mt-2 text-xs leading-6 text-slate-400">Setelah disimpan, kategori langsung tersedia saat menambah atau mengedit produk.</p>
        @include('pages.admin.categories.partials.form')
    </section>
</div>
@endsection
