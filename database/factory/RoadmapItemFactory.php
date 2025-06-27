<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoadmapItem>
 */
class RoadmapItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'slug' => $this->faker->unique()->slug,
            'description' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(['planned', 'approved', 'in_progress', 'completed', 'cancelled', 'declined']),
            'type' => $this->faker->randomElement(['feature', 'bug']),
            'upvotes' => $this->faker->numberBetween(0, 100),

            // Get a random user ID from the User factory
            'user_id' => User::query()->inRandomOrder()->first()->id,
        ];
    }
}
