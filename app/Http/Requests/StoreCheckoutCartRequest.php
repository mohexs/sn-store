<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $payload = $this->input('cart_payload');
        $items = is_string($payload) ? json_decode($payload, true) : null;

        $this->merge(['cart' => is_array($items) ? $items : null]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cart_payload' => ['required', 'string', 'max:20000'],
            'cart' => ['required', 'array', 'list', 'min:1', 'max:50'],
            'cart.*' => ['required', 'array:id,quantity'],
            'cart.*.id' => ['required', 'integer', 'distinct', Rule::in(array_column(config('storefront.products'), 'id'))],
            'cart.*.quantity' => ['required', 'integer', 'between:1,99'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'cart' => 'keranjang',
            'cart.*.id' => 'produk',
            'cart.*.quantity' => 'jumlah produk',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'cart.required' => 'Keranjang belanja kosong atau tidak valid.',
            'cart.*.id.in' => 'Produk tidak tersedia di katalog.',
            'cart.*.quantity.between' => 'Jumlah setiap produk harus antara 1 dan 99.',
        ];
    }
}
