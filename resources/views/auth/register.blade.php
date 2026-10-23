@extends('layouts.public')

@section('title', 'Daftar — SN Store')

@section('content')
<section class="mx-auto grid min-h-[65vh] max-w-7xl items-center gap-10 px-5 py-12 sm:px-8 lg:grid-cols-2 lg:gap-16 lg:py-20">
    <div class="relative hidden min-h-[500px] overflow-hidden rounded-3xl bg-[#b8273a] p-10 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full border-[45px] border-white/10" aria-hidden="true"></div>
        <div class="relative">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3 py-2 text-[10px] font-semibold uppercase tracking-[.15em]">
                <span class="h-1.5 w-1.5 rounded-full bg-rose-200"></span>
                Bergabung bersama kami
            </span>
            <h1 class="mt-8 max-w-md text-4xl font-extrabold leading-tight tracking-tight">Mulai langkah sehat Anda bersama SN Store.</h1>
            <p class="mt-5 max-w-md text-sm leading-7 text-rose-100">Buat akun untuk menjelajahi pilihan kebutuhan kesehatan untuk Anda dan keluarga.</p>
        </div>
        <div class="relative flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-xl font-black text-rose-700">sn<span class="text-rose-300">+</span></span>
            <span class="text-sm font-semibold tracking-wide">SN STORE <span class="text-rose-200">/</span> HEALTH & CARE</span>
        </div>
    </div>

    <div class="mx-auto w-full max-w-md">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-[.2em] text-rose-700">Akun SN Store</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">Buat akun baru</h2>
            <p class="mt-3 text-sm leading-6 text-slate-500">Daftar dengan mengisi informasi akun Anda.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-5">
            @csrf

            <div>
                <label for="name" class="text-sm font-semibold text-slate-700">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20">
                <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-rose-700" />
            </div>

            <div>
                <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20">
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-700" />
            </div>

            <div>
                <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20">
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-700" />
            </div>

            <div>
                <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Konfirmasi password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-rose-700" />
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-rose-700 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-rose-800 focus-visible:outline-rose-700">
                Daftar
                <span class="ml-2" aria-hidden="true">↗</span>
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-slate-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-bold text-rose-700 transition hover:text-rose-800">Masuk</a>
        </p>
    </div>
</section>
@endsection
