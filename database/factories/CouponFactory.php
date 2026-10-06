<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['percent', 'fixed']);

        return [
            'code' => strtoupper(Str::random(8)),
            'type' => $type,
            'value' => $type === 'percent'
                ? fake()->numberBetween(5, 50)           // 5%–50%
                : fake()->numberBetween(10000, 200000),  // 10K–200K VNĐ
            'min_order_amount' => fake()->randomElement([0, 100000, 200000, 500000]),
            'max_discount' => $type === 'percent'
                ? fake()->randomElement([50000, 100000, 200000, null])
                : null,
            'start_at' => now()->subDays(fake()->numberBetween(0, 30)),
            'end_at' => now()->addDays(fake()->numberBetween(7, 90)),
            'usage_limit' => fake()->randomElement([null, 10, 50, 100]),
            'used_count' => 0,
            'status' => 'active',
        ];
    }

    public function percent(int $value = 10): static
    {
        return $this->state(fn () => [
            'type' => 'percent',
            'value' => $value,
        ]);
    }

    public function fixed(int $amount = 50000): static
    {
        return $this->state(fn () => [
            'type' => 'fixed',
            'value' => $amount,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'end_at' => now()->subDay(),
            'status' => 'inactive',
        ]);
    }
}
