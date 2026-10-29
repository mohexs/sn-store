<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAdminAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage admin accounts') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'lowercase', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+().\s-]+$/'],
            'password' => ['required', 'string', 'confirmed', Password::min(8), 'max:255'],
            'role' => ['required', Rule::in([User::ADMIN_ROLE, User::TRANSACTION_PROCESSING_ADMIN_ROLE])],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'phone' => 'nomor telepon',
            'role' => 'jenis akses',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.lowercase' => 'Email harus menggunakan huruf kecil.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'role.required' => 'Pilih jenis akses admin.',
            'role.in' => 'Jenis akses admin yang dipilih tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
