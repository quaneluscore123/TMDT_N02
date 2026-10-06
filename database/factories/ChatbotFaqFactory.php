<?php

namespace Database\Factories;

use App\Models\ChatbotFaq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatbotFaq>
 */
class ChatbotFaqFactory extends Factory
{
    protected $model = ChatbotFaq::class;

    public function definition(): array
    {
        return [
            'question' => fake()->sentence(),
            'answer' => fake()->paragraph(),
            'keywords' => implode(',', fake()->words(3)),
            'status' => 'active',
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
