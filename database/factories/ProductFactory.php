<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => mb_strtoupper(fake()->unique()->bothify('SN-####??')),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'category_id' => Category::factory(),
            'weight' => 250,
            'length' => 12,
            'width' => 8,
            'height' => 6,
            'price' => 45000,
            'barcode' => fake()->unique()->ean13(),
            'rack_location' => 'A-01',
            'principal_name' => 'PT Sehat Bersama',
        ];
    }
}
