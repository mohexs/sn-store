@extends('layouts.admin')
@section('title', 'Tambah Admin')

@section('content')
<a href="{{ route('admin.admin-accounts.index') }}" class="mb-5 inline-flex text-sm font-semibold text-brand-700 hover:underline">&larr; Kembali ke daftar admin</a>
<div class="mb-6"><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Pengelola toko</p><h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Buat akun admin</h1><p class="mt-2 text-sm text-slate-500">Tambahkan pengelola baru untuk membantu operasional toko.</p></div>
<div class="grid items-start gap-6 lg:grid-cols-3">
    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 lg:col-span-2" aria-labelledby="admin-form-title">
        <h2 id="admin-form-title" class="text-sm font-bold">Informasi admin baru</h2>
        <form method="POST" action="{{ route('admin.admin-accounts.store') }}" class="mt-5 space-y-5">
            @csrf
            @foreach (['name' => 'Nama lengkap', 'email' => 'Email', 'phone' => 'Nomor telepon (opsional)', 'password' => 'Password', 'password_confirmation' => 'Konfirmasi password'] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="mb-2 block text-xs font-semibold text-slate-600">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="{{ str_starts_with($field, 'password') ? 'password' : ($field === 'email' ? 'email' : ($field === 'phone' ? 'tel' : 'text')) }}" @if (! str_starts_with($field, 'password')) value="{{ old($field) }}" @endif maxlength="{{ $field === 'phone' ? 30 : 255 }}" @required($field !== 'phone') @if (str_starts_with($field, 'password')) minlength="8" autocomplete="new-password" @else autocomplete="{{ ['name' => 'name', 'email' => 'email', 'phone' => 'tel'][$field] }}" @endif aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" @error($field) aria-describedby="{{ $field }}-error" @enderror class="w-full rounded-xl border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error($field)<p id="{{ $field }}-error" class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
            <p class="text-xs leading-5 text-slate-500">Gunakan email yang belum terdaftar dan password minimal 8 karakter.</p>
            <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-5"><button type="submit" class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-800">Buat akun admin</button><a href="{{ route('admin.admin-accounts.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-slate-500 hover:bg-slate-50">Batal</a></div>
        </form>
    </section>
    <aside class="rounded-2xl border border-brand-100 bg-brand-50 p-6"><span class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-white text-brand-700"><x-admin.icon name="shield" /></span><h2 class="text-sm font-bold text-brand-950">Akses pengelola</h2><p class="mt-3 text-xs leading-6 text-brand-900/80">Akun ini mendapat hak admin, termasuk mengelola data customer dan membuat akun admin lainnya.</p><p class="mt-3 text-xs leading-6 text-brand-900/80">Setelah dibuat, admin baru dapat login dengan email dan password yang Anda tentukan. Sampaikan kredensial secara pribadi kepada pemilik akun.</p></aside>
</div>
@endsection
