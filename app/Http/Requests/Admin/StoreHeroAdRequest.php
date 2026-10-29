<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreHeroAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage ads') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'eyebrow' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:255'],
            'accent' => ['required', 'string', 'max:255'],
            'copy' => ['required', 'string', 'max:2000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
            'sort_order' => ['required', 'integer', 'between:0,100'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'eyebrow' => 'label kecil',
            'title' => 'judul banner',
            'accent' => 'judul aksen',
            'copy' => 'keterangan banner',
            'image' => 'foto banner',
            'sort_order' => 'urutan tampil',
            'is_active' => 'status banner',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max.string' => ':attribute maksimal :max karakter.',
            'image.image' => 'File banner harus berupa gambar.',
            'image.mimes' => 'Gunakan gambar JPG, JPEG, PNG, atau WebP.',
            'image.extensions' => 'Ekstensi banner harus JPG, JPEG, PNG, atau WebP.',
            'image.max' => 'Ukuran file banner maksimal 2 MB.',
            'image.dimensions' => 'Dimensi banner maksimal 4096 × 4096 piksel.',
            'sort_order.between' => 'Urutan tampil harus antara 0 dan 100.',
            'is_active.boolean' => 'Status banner tidak valid.',
        ];
    }
}
