<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage promotions') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'discount_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'discount_value' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999999999.99',
                'decimal:0,2',
                Rule::when($this->input('discount_type') === 'percentage', ['max:100']),
            ],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'nama program',
            'description' => 'deskripsi',
            'discount_type' => 'jenis diskon',
            'discount_value' => 'nilai diskon',
            'starts_at' => 'waktu mulai',
            'ends_at' => 'waktu berakhir',
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
            'discount_type.in' => 'Pilih jenis diskon yang tersedia.',
            'discount_value.max' => 'Nilai diskon persentase maksimal 100%.',
            'starts_at.date' => 'Waktu mulai harus berupa tanggal dan waktu yang valid.',
            'ends_at.date' => 'Waktu berakhir harus berupa tanggal dan waktu yang valid.',
            'ends_at.after' => 'Waktu berakhir harus setelah waktu mulai.',
        ];
    }
}
