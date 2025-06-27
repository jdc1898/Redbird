<?php

namespace Database\Factories;

use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()->id ?? \App\Models\User::factory(),
            'type' => $this->faker->randomElement(['daily', 'weekly', 'monthly', 'yearly', 'lifetime']),
            'product_id' => \App\Models\Product::inRandomOrder()->first()->id ?? \App\Models\Product::factory(),
            'stripe_id' => Price::factory()->create()->stripe_price_id,
            'stripe_status' => $this->faker->randomElement(['incomplete', 'incomplete_expired', 'trialing', 'active', 'past_due', 'past_due', 'canceled', 'unpaid', 'paused']),
            'stripe_price' => 'price_'.Str::random(10),
            'quantity' => 1,
            'trial_ends_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'ends_at' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
