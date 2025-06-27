<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'status' => 'pending',
            'total_amount' => $this->faker->numberBetween(1000, 100000),
            'total_after_discount' => $this->faker->numberBetween(500, 90000),
            'total_discount' => $this->faker->numberBetween(0, 5000),
            'payment_provider' => $this->faker->randomElement(['stripe', 'check', 'bank_transfer']),
        ];
    }
}
