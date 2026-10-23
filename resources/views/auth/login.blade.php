@extends('layouts.public')

@section('title', 'Masuk — SN Store')

@section('content')
<section class="mx-auto grid min-h-[65vh] max-w-7xl items-center gap-10 px-5 py-12 sm:px-8 lg:grid-cols-2 lg:gap-16 lg:py-20">
    <div class="relative hidden min-h-[460px] overflow-hidden rounded-3xl bg-[#b8273a] p-10 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full border-[45px] border-white/10" aria-hidden="true"></div>
        <div class="relative">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3 py-2 text-[10px] font-semibold uppercase tracking-[.15em]">
                <span class="h-1.5 w-1.5 rounded-full bg-rose-200"></span>
                Selamat datang kembali
            </span>
            <h1 class="mt-8 max-w-md text-4xl font-extrabold leading-tight tracking-tight">Teman sehat Anda, selalu dekat.</h1>
            <p class="mt-5 max-w-md text-sm leading-7 text-rose-100">Masuk untuk melanjutkan perjalanan Anda bersama SN Store.</p>
        </div>
        <div class="relative flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-xl font-black text-rose-700">sn<span class="text-rose-300">+</span></span>
            <span class="text-sm font-semibold tracking-wide">SN STORE <span class="text-rose-200">/</span> HEALTH & CARE</span>
        </div>
    </div>

    <div class="mx-auto w-full max-w-md">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-[.2em] text-rose-700">Akun SN Store</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">Masuk ke akun Anda</h2>
            <p class="mt-3 text-sm leading-6 text-slate-500">Gunakan email dan password yang terdaftar untuk melanjutkan.</p>
        </div>

        <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
            @csrf

            <div>
                <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20">
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-700" />
            </div>

            <div>
                <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-700/20">
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-700" />
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-rose-700 focus:ring-rose-700">
                    Ingat saya
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-rose-700 transition hover:text-rose-800">Lupa password?</a>
                @endif
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-rose-700 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-rose-800 focus-visible:outline-rose-700">
                Masuk
                <span class="ml-2" aria-hidden="true">↗</span>
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-slate-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-bold text-rose-700 transition hover:text-rose-800">Daftar sekarang</a>
        </p>
    </div>
</section>
@endsection
