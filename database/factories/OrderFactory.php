<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_code' => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'subtotal' => fake()->numberBetween(100000, 5000000),
            'discount' => 0,
            'shipping_fee' => 30000,
            'total' => fake()->numberBetween(130000, 5030000),
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_name' => fake()->name(),
            'shipping_phone' => fake()->phoneNumber(),
            'shipping_address' => fake()->address(),
            'note' => null,
        ];
    }
}
