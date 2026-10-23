<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => fake()->unique()->bothify('SN-########'),
            'customer_name' => fake()->name(),
            'sale_date' => now('Asia/Makassar')->toDateString(),
            'status' => 'paid',
        ];
    }
}
