@extends('layouts.admin')
@section('title', 'Tambah Produk')

@section('content')
@include('pages.admin.products.partials.heading', ['heading' => 'Tambah produk', 'description' => 'Lengkapi detail produk untuk menyusun katalog toko Anda.'])
<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" x-data="productForm" @submit="submitting = true">
    @csrf
    @include('pages.admin.products.partials.form')
</form>
@endsection
