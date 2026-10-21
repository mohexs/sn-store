<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'description'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected function name(): Attribute
    {
        return Attribute::make(set: fn (string $value): array => [
            'name' => Str::squish($value),
            'name_key' => mb_strtolower(Str::squish($value)),
        ]);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
