<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('sku'))) {
            $this->merge(['sku' => mb_strtoupper(trim($this->input('sku')))]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9][A-Z0-9._-]*$/', Rule::unique(Product::class, 'sku')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'category_id' => ['required', 'integer', Rule::exists(Category::class, 'id')],
            'weight' => ['required', 'numeric', 'gt:0', 'max:9999999999.99', 'decimal:0,2'],
            'length' => ['required', 'numeric', 'gt:0', 'max:99999999.99', 'decimal:0,2'],
            'width' => ['required', 'numeric', 'gt:0', 'max:99999999.99', 'decimal:0,2'],
            'height' => ['required', 'numeric', 'gt:0', 'max:99999999.99', 'decimal:0,2'],
            'price' => ['required', 'numeric', 'gt:0', 'max:999999999999.99', 'decimal:0,2'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
            'barcode' => ['nullable', 'string', 'max:128', Rule::unique(Product::class, 'barcode')],
            'rack_location' => ['nullable', 'string', 'max:100'],
            'principal_name' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'sku' => 'SKU', 'name' => 'nama produk', 'description' => 'deskripsi',
            'category_id' => 'kategori produk', 'weight' => 'berat produk',
            'length' => 'panjang', 'width' => 'lebar', 'height' => 'tinggi',
            'price' => 'harga produk', 'photo' => 'foto produk', 'barcode' => 'barcode',
            'rack_location' => 'lokasi rak', 'principal_name' => 'nama principal',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'numeric' => ':attribute harus berupa angka.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.numeric' => ':attribute maksimal :max.',
            'gt' => ':attribute harus lebih besar dari 0.',
            'decimal' => ':attribute maksimal memiliki 2 angka desimal.',
            'sku.regex' => 'SKU hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
            'sku.unique' => 'SKU sudah digunakan oleh produk lain.',
            'barcode.unique' => 'Barcode sudah digunakan oleh produk lain.',
            'category_id.exists' => 'Pilih kategori produk yang tersedia.',
            'photo.image' => 'Foto produk harus berupa gambar.',
            'photo.mimes' => 'Gunakan foto JPG, PNG, atau WebP.',
            'photo.extensions' => 'Ekstensi foto harus JPG, JPEG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto produk maksimal 2 MB.',
            'photo.dimensions' => 'Dimensi foto produk maksimal 4096 × 4096 piksel.',
        ];
    }
}
