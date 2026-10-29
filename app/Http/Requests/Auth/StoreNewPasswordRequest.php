<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class StoreNewPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults(), 'max:255'],
        ];
    }

    /** @return array<string, mixed> */
    public function credentials(): array
    {
        return $this->safe()->only(['email', 'password', 'token']);
    }
}
