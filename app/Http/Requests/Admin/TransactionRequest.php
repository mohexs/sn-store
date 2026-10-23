<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['pick', 'save', 'process', 'pack', 'ship'])],
            'quantities' => ['required_if:action,save,process', 'array'],
            'quantities.*' => ['required', 'integer', 'min:0'],
            'shipping_carrier' => ['required_if:action,ship', 'nullable', 'string', 'max:100'],
            'tracking_number' => ['required_if:action,ship', 'nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantities.required_if' => 'Isi qty proses setiap barang.',
            'quantities.*.integer' => 'Qty proses harus berupa bilangan bulat.',
            'quantities.*.min' => 'Qty proses tidak boleh negatif.',
            'shipping_carrier.required_if' => 'Nama kurir wajib diisi.',
            'tracking_number.required_if' => 'Nomor resi wajib diisi.',
        ];
    }
}
