<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'max_tickets' => fake()->numberBetween(10, 9999),
            'price_per_ticket' => fake()->numberBetween(10000, 99999),
            'price' => fake()->numberBetween(10000, 1000000),
            'note' => fake()->sentence(),
            'expires_in' => fake()->numberBetween(30, 600)
        ];
    }
}
