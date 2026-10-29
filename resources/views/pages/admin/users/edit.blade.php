@extends('layouts.admin')
@section('title', 'Profil customer')

@section('content')
<a href="{{ route('admin.users.index') }}" class="mb-5 inline-flex text-sm font-semibold text-brand-700 hover:underline">&larr; Kembali ke daftar user</a>
<div class="mb-6"><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Customer #{{ $customer->id }}</p><h1 class="break-words text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $customer->name }}</h1><p class="mt-2 text-sm text-slate-500">Profil, kontak, dan alamat pengiriman customer.</p></div>
@if (session('status'))<div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('status') }}</div>@endif
<div class="grid items-start gap-6 lg:grid-cols-3">
    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 lg:col-span-2" aria-labelledby="profile-title">
        <h2 id="profile-title" class="text-sm font-bold">Informasi customer</h2>
        <form method="POST" action="{{ route('admin.users.update', $customer) }}" class="mt-5 space-y-5">
            @csrf
            @method('PATCH')
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach (['name' => 'Nama lengkap', 'email' => 'Email / akun user', 'phone' => 'Nomor telepon', 'city' => 'Kota/Kabupaten', 'province' => 'Provinsi', 'postal_code' => 'Kode pos'] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="mb-2 block text-xs font-semibold text-slate-600">{{ $label }}{{ in_array($field, ['name', 'email']) ? ' *' : '' }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : ($field === 'phone' ? 'tel' : 'text') }}" value="{{ old($field, $customer->$field) }}" maxlength="{{ ['name' => 255, 'email' => 255, 'phone' => 30, 'city' => 100, 'province' => 100, 'postal_code' => 5][$field] }}" @required(in_array($field, ['name', 'email'])) @if ($field === 'postal_code') inputmode="numeric" pattern="[0-9]{5}" @endif aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" @error($field) aria-describedby="{{ $field }}-error" @enderror class="w-full rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                        @error($field)<p id="{{ $field }}-error" class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
            <div><label for="address" class="mb-2 block text-xs font-semibold text-slate-600">Alamat lengkap pengiriman</label><textarea id="address" name="address" rows="4" maxlength="2000" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, dan kecamatan" aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}" @error('address') aria-describedby="address-error" @enderror class="w-full rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('address', $customer->address) }}</textarea>@error('address')<p id="address-error" class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror</div>
            <p class="text-xs leading-5 text-slate-500">Nama dan email wajib diisi. Perubahan email akan menghapus status verifikasi email sebelumnya.</p>
            <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5"><button class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-800">Simpan perubahan</button><a href="{{ route('admin.users.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-slate-500 hover:bg-slate-50">Batal</a></div>
        </form>
    </section>
    <aside class="rounded-2xl border border-slate-200/80 bg-white p-6"><h2 class="text-sm font-bold">Informasi akun</h2><dl class="mt-5 space-y-5 text-xs"><div><dt class="text-slate-400">ID user</dt><dd class="mt-1.5 font-semibold">#{{ $customer->id }}</dd></div><div><dt class="text-slate-400">Tanggal daftar</dt><dd class="mt-1.5 font-semibold">{{ $customer->created_at?->format('d/m/Y H:i') ?? '—' }}</dd></div><div><dt class="text-slate-400">Terakhir diperbarui</dt><dd class="mt-1.5 font-semibold">{{ $customer->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd></div><div><dt class="text-slate-400">Status email</dt><dd class="mt-1.5 font-semibold">{{ $customer->email_verified_at ? 'Terverifikasi' : 'Belum terverifikasi' }}</dd></div></dl></aside>
</div>
@endsection
