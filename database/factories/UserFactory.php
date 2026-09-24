<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake('vi_VN')->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'customer',
            'is_active' => true,
            'referral_code' => strtoupper(Str::random(8)),
            'avatar' => null,
            'provider' => null,
            'provider_id' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function googleUser(): static
    {
        return $this->state(fn () => [
            'provider' => 'google',
            'provider_id' => fake()->numerify('####################'),
            'password' => null,
        ]);
    }
}
