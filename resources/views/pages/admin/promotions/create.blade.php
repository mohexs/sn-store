@extends('layouts.admin')
@section('title', 'Tambah Promosi')

@section('content')
<div class="mb-6"><a href="{{ route('admin.promotions.index') }}" class="mb-5 inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-brand-700"><span aria-hidden="true">←</span>Kembali ke promosi</a><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Program penjualan</p><h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Tambah program diskon</h1><p class="mt-2 text-sm text-slate-500">Program dapat dibuat sebelum periode diskon dimulai.</p></div>
<form method="POST" action="{{ route('admin.promotions.store') }}">
    @csrf
    @include('pages.admin.promotions.partials.form')
</form>
@endsection
