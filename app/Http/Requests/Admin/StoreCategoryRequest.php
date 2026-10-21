<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => Str::squish($this->input('name'))]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'max:100', function (string $attribute, mixed $value, Closure $fail): void {
                $query = Category::query()->where('name_key', mb_strtolower($value));

                if ($this->categoryToIgnore() !== null) {
                    $query->whereKeyNot($this->categoryToIgnore()->getKey());
                }

                if ($query->exists()) {
                    $fail('Nama kategori sudah digunakan.');
                }
            }],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function categoryToIgnore(): ?Category
    {
        return null;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.string' => 'Nama kategori harus berupa teks.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
        ];
    }
}
