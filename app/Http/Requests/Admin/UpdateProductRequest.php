<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends StoreProductRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'sku' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9][A-Z0-9._-]*$/', Rule::unique(Product::class, 'sku')->ignore($this->route('product'))],
            'barcode' => ['nullable', 'string', 'max:128', Rule::unique(Product::class, 'barcode')->ignore($this->route('product'))],
        ]);
    }
}
