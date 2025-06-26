<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Price>
 */
class PriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'stripe_price_id' => 'price_'.$this->faker->unique()->lexify('??????????'),
            'valid_from' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'valid_until' => $this->faker->dateTimeBetween('now', '+1 year'),
        ];
    }
}
