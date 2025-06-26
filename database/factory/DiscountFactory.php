<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discount>
 */
class DiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'type' => 'fixed',
            'amount' => $this->faker->numberBetween(1, 100),
            'valid_until' => $this->faker->dateTimeBetween('now', '+3 months'),
            'plan_id' => null,
            'max_redemptions' => 1,
            'has_promo_codes' => false,
            'max_redemptions_per_user' => 1,
            'is_recurring' => $this->faker->boolean(),
            'is_active' => $this->faker->boolean(),
            'duration_in_months' => 1,
            'maximum_recurring_intervals' => 1,
        ];
    }
}
