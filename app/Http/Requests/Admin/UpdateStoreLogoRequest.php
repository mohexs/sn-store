<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view themes') ?? false;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'logo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=4096,max_height=4096',
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'logo.required' => 'Pilih file gambar identitas toko yang memuat logo dan nama toko.',
            'logo.image' => 'Gambar identitas toko harus berupa gambar.',
            'logo.mimes' => 'Gunakan logo JPG, PNG, atau WebP.',
            'logo.extensions' => 'Ekstensi logo harus JPG, JPEG, PNG, atau WebP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'logo.dimensions' => 'Dimensi logo maksimal 4096 × 4096 piksel.',
        ];
    }
}
