<?php

namespace App\Http\Requests\Admin;

class UpdateHeroAdRequest extends StoreHeroAdRequest
{
    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
        ]);
    }
}
