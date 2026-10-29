@extends('layouts.admin')
@section('title', 'Akun Admin')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Pengelola toko</p><h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Akun admin</h1><p class="mt-2 text-sm text-slate-500">Daftar akun yang memiliki akses sebagai admin toko.</p></div>
    <a href="{{ route('admin.admin-accounts.create') }}" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-800">Tambah admin</a>
</div>
@if (session('status'))<div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('status') }}</div>@endif
<section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/20" aria-labelledby="admins-title">
    <div class="border-b border-slate-100 p-6"><h2 id="admins-title" class="text-sm font-bold">Daftar admin</h2><p class="mt-1.5 text-xs text-slate-500">{{ $admins->total() }} akun admin terdaftar.</p></div>
    <div class="overflow-x-auto"><table class="w-full text-left text-xs"><caption class="sr-only">Akun admin pengelola toko</caption><thead class="border-b border-slate-100 bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500"><tr><th scope="col" class="px-6 py-3">Nama</th><th scope="col" class="px-6 py-3">Email</th><th scope="col" class="px-6 py-3">Jenis akses</th><th scope="col" class="px-6 py-3">Telepon</th><th scope="col" class="px-6 py-3">Tanggal dibuat</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse ($admins as $admin)
            <tr class="hover:bg-slate-50/60"><td class="px-6 py-4 font-semibold">{{ $admin->name }} @if ($admin->is(auth()->user()))<span class="ml-2 rounded-full bg-brand-50 px-2 py-1 text-[10px] text-brand-700">Anda</span>@endif</td><td class="px-6 py-4 text-slate-600">{{ $admin->email }}</td><td class="px-6 py-4 text-slate-500">{{ $admin->hasRole(\App\Models\User::TRANSACTION_PROCESSING_ADMIN_ROLE) ? 'Admin pemrosesan transaksi' : 'Administrator penuh' }}</td><td class="px-6 py-4 text-slate-500">{{ $admin->phone ?: 'Belum diisi' }}</td><td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $admin->created_at?->format('d/m/Y') ?? '—' }}</td></tr>
        @empty
            <tr><td colspan="5" class="px-6 py-14 text-center text-slate-400">Belum ada akun admin.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="border-t border-slate-100 px-6 py-4">{{ $admins->links() }}</div>
</section>
@endsection
