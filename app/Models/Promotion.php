<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'name',
        'description',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected function status(): Attribute
    {
        return Attribute::get(function (): string {
            if (now()->lt($this->starts_at)) {
                return 'upcoming';
            }

            if (now()->gt($this->ends_at)) {
                return 'completed';
            }

            return 'running';
        });
    }
}
