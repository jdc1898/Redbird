<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->sentence(10),
            'content' => $this->faker->paragraph(5),
            'slug' => $this->faker->slug,

            // Get a random Blog Category ID from the BlogCategory factory
            'blog_category_id' => \App\Models\BlogCategory::factory()->create()->id,

            // Get a random user ID from the User factory
            'user_id' => User::query()->inRandomOrder()->first()->id,

            'image' => $this->faker->imageUrl(640, 480, 'blog', true, 'Faker'),
            'is_published' => $this->faker->boolean(80),
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
