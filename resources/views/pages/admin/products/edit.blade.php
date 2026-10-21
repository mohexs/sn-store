@extends('layouts.admin')
@section('title', 'Edit Produk')

@section('content')
@include('pages.admin.products.partials.heading', ['heading' => 'Edit produk', 'description' => 'Perbarui informasi produk agar data toko tetap akurat.'])
<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" x-data="productForm" @submit="submitting = true">
    @csrf
    @method('PUT')
    @include('pages.admin.products.partials.form')
</form>
@endsection
