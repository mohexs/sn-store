<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['cart' => $this->session()->get('checkout.cart', [])]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cart' => ['bail', 'required', 'array', 'min:1'],
            'customer_name' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(['bank_transfer'])],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('checkout.index');
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'customer_name' => 'nama penerima',
            'payment_method' => 'metode pembayaran',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'cart.required' => 'Keranjang belanja kosong. Silakan pilih produk terlebih dahulu.',
            'cart.min' => 'Keranjang belanja kosong. Silakan pilih produk terlebih dahulu.',
            'required' => ':attribute wajib diisi.',
            'customer_name.max' => ':attribute maksimal 255 karakter.',
            'payment_method.in' => 'Pilih metode pembayaran yang tersedia.',
        ];
    }
}
