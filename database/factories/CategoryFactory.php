<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake('vi_VN')->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.Str::random(4),
            'parent_id' => null,
            'image' => null,
            'sort_order' => fake()->numberBetween(1, 100),
            'status' => 'active',
        ];
    }

    public function child(int $parentId): static
    {
        return $this->state(fn () => ['parent_id' => $parentId]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
