@extends('layouts.admin')
@section('title', 'Edit Banner Ads')

@section('content')
<div class="mb-6"><a href="{{ route('admin.ads.index') }}" class="mb-5 inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-brand-700"><span aria-hidden="true">←</span>Kembali ke Ads</a><p class="mb-1 text-[10px] font-bold uppercase tracking-[.2em] text-brand-700">Konten etalase</p><h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Edit banner hero</h1><p class="mt-2 text-sm text-slate-500">Perbarui keterangan, gambar, urutan, atau status banner.</p></div>
<form method="POST" action="{{ route('admin.ads.update', $ad) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('pages.admin.ads.partials.form')
</form>
@endsection
