<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

        if (is_array($this->input('variations'))) {
            $this->merge([
                'variations' => collect($this->input('variations'))
                    ->map(fn (mixed $variation): mixed => is_array($variation) && is_string($variation['sku'] ?? null)
                        ? [...$variation, 'sku' => mb_strtoupper(trim($variation['sku']))]
                        : $variation)
                    ->all(),
            ]);
        } elseif ($this->has('variations_present')) {
            $this->merge(['variations' => []]);
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
            'variations_present' => ['sometimes', 'accepted'],
            'variations' => ['sometimes', 'array', 'max:50'],
            'variations.*.name' => ['required', 'string', 'max:255'],
            'variations.*.sku' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9][A-Z0-9._-]*$/', 'distinct'],
            'variations.*.price' => ['required', 'numeric', 'gt:0', 'max:999999999999.99', 'decimal:0,2'],
            'variations.*.weight' => ['nullable', 'numeric', 'gt:0', 'max:9999999999.99', 'decimal:0,2'],
        ];
    }

    /** @return array<int, \Closure(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $variationSkus = collect($this->input('variations', []))->pluck('sku')->filter()->values();
                $product = $this->route('product');
                $productId = $product instanceof Product ? $product->getKey() : null;
                $baseSku = $this->input('sku');

                if (ProductVariation::query()->where('sku', $baseSku)->exists()) {
                    $validator->errors()->add('sku', 'SKU sudah digunakan oleh variasi produk lain.');
                }

                if ($variationSkus->isEmpty()) {
                    return;
                }

                if ($variationSkus->contains($baseSku)) {
                    $validator->errors()->add('variations.0.sku', 'SKU variasi harus berbeda dari SKU utama produk.');
                }

                if (Product::query()->whereIn('sku', $variationSkus)->where('id', '!=', $productId ?? 0)->exists()) {
                    $validator->errors()->add('variations.0.sku', 'SKU variasi sudah digunakan oleh produk lain.');
                }

                if (ProductVariation::query()
                    ->whereIn('sku', $variationSkus)
                    ->when($productId !== null, fn ($query) => $query->where('product_id', '!=', $productId))
                    ->exists()) {
                    $validator->errors()->add('variations.0.sku', 'SKU variasi sudah digunakan oleh variasi produk lain.');
                }

            },
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
            'variations.*.name' => 'nama variasi', 'variations.*.sku' => 'SKU variasi',
            'variations.*.price' => 'harga variasi', 'variations.*.weight' => 'berat variasi',
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
            'variations.*.sku.regex' => 'SKU variasi hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
            'variations.*.sku.distinct' => 'Setiap variasi harus memiliki SKU yang berbeda.',
            'category_id.exists' => 'Pilih kategori produk yang tersedia.',
            'photo.image' => 'Foto produk harus berupa gambar.',
            'photo.mimes' => 'Gunakan foto JPG, PNG, atau WebP.',
            'photo.extensions' => 'Ekstensi foto harus JPG, JPEG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto produk maksimal 2 MB.',
            'photo.dimensions' => 'Dimensi foto produk maksimal 4096 × 4096 piksel.',
        ];
    }
}
