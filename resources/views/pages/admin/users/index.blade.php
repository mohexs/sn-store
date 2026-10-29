@extends('layouts.admin')
@section('title', 'User')

@section('content')
<div class="mb-6"><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Pelanggan toko</p><h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">User & customer</h1><p class="mt-2 text-sm text-slate-500">Kelola profil dan alamat customer yang terdaftar untuk berbelanja.</p></div>
<section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/20" aria-labelledby="customers-title">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-5 sm:p-6"><div><h2 id="customers-title" class="text-sm font-bold">Daftar customer</h2><p class="mt-1.5 text-xs text-slate-500">{{ $users->total() }} customer{{ $search !== '' ? ' ditemukan' : ' terdaftar' }}. Akun admin dikelola terpisah.</p></div>
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex w-full flex-wrap items-end gap-2 sm:w-auto">
            <div class="min-w-0 flex-1"><label for="customer-search" class="mb-1 block text-xs font-semibold text-slate-600">Cari customer</label><input id="customer-search" type="search" name="search" value="{{ $search }}" maxlength="100" placeholder="Nama, email, telepon, atau kota" class="w-full rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500 sm:w-72"></div>
            <button class="rounded-xl bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-800">Cari</button>
            @if ($search !== '')<a href="{{ route('admin.users.index') }}" class="px-2 py-2.5 text-sm font-semibold text-slate-500">Reset</a>@endif
        </form>
    </div>
    @error('search')<p role="alert" class="px-6 py-3 text-sm text-red-600">{{ $message }}</p>@enderror
    <div class="overflow-x-auto"><table class="w-full text-left text-xs"><caption class="sr-only">Profil customer yang terdaftar untuk berbelanja</caption><thead class="border-b border-slate-100 bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500"><tr><th scope="col" class="px-6 py-3">User / Nama</th><th scope="col" class="px-6 py-3">Kontak</th><th scope="col" class="px-6 py-3">Alamat</th><th scope="col" class="px-6 py-3">Terdaftar</th><th scope="col" class="px-6 py-3 text-right">Aksi</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse ($users as $customer)
            <tr class="hover:bg-slate-50/60">
                <td class="px-6 py-4"><p class="font-semibold text-slate-800">{{ $customer->name }}</p><p class="mt-1 text-slate-400">User #{{ $customer->id }}</p></td>
                <td class="px-6 py-4"><p>{{ $customer->email }}</p><p class="mt-1 text-slate-500">{{ $customer->phone ?: 'Telepon belum diisi' }}</p><span class="mt-2 inline-flex rounded-full bg-slate-100 px-2 py-1 text-[10px] text-slate-600">{{ $customer->email_verified_at ? 'Email terverifikasi' : 'Email belum terverifikasi' }}</span></td>
                <td class="min-w-[200px] max-w-xs px-6 py-4"><p class="line-clamp-2 text-slate-600">{{ $customer->address ?: 'Alamat belum diisi' }}</p><p class="mt-1 text-slate-400">{{ collect([$customer->city, $customer->province, $customer->postal_code])->filter()->implode(', ') }}</p></td>
                <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $customer->created_at?->format('d/m/Y') ?? '—' }}</td>
                <td class="px-6 py-4 text-right"><a href="{{ route('admin.users.edit', $customer) }}" aria-label="Detail profil {{ $customer->name }}" class="inline-flex whitespace-nowrap rounded-lg border border-brand-100 px-3 py-2 font-semibold text-brand-700 hover:bg-brand-50">Detail & edit</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-6 py-14 text-center text-slate-400">{{ $search !== '' ? 'Tidak ada customer yang cocok dengan pencarian.' : 'Belum ada customer terdaftar.' }}</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="border-t border-slate-100 px-6 py-4">{{ $users->links() }}</div>
</section>
@endsection
