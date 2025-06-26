<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            'Red bird',
            'Blue bird',
            'Green bird',
            'Yellow bird',
            'Black bird',
            'White bird',
            'Purple bird',
            'Orange bird',
            'Pink bird',
            'Brown bird',
            'Gray bird',
            'Silver bird',
            'Gold bird',
            'Teal bird',
            'Cyan bird',
            'Magenta bird',
        ];

        $name = $this->faker->unique()->randomElement($names);
        $slug = str($name)->slug();
        $colorBird = explode(' ', $name);

        $shippable = $this->faker->boolean(5);

        if ($shippable) {
            $packageDimensions = [
                'length' => $this->faker->randomFloat(2, 0.1, 10),
                'width' => $this->faker->randomFloat(2, 0.1, 10),
                'height' => $this->faker->randomFloat(2, 0.1, 10),
            ];
        } else {
            $packageDimensions = null;
        }

        return [
            'name' => $name,
            'active' => false,
            'description' => $this->faker->paragraph(3),
            'metadata' => [
                'color' => $colorBird[0],
                'size' => $this->faker->randomElement(['small', 'medium', 'large']),
                'weight' => $this->faker->randomFloat(2, 0.1, 10),
            ],
            'tax_code' => 'txcd_10701300',
            'images' => [
                'https://placehold.co/600x400',
                'https://placehold.co/600x400',
            ],
            'marketing_features' => [
                ['name' => $this->faker->sentence(1, true)],
                ['name' => $this->faker->sentence(1, true)],
                ['name' => $this->faker->sentence(1, true)],
            ],
            'package_dimensions' => $packageDimensions,
            'shippable' => $shippable,
            'statement_descriptor' => $this->faker->word(),
            'unit_label' => $this->faker->randomElement(['seat', 'tier', 'unit', 'package', 'box']),
            'url' => $this->faker->url(),
            'slug' => $slug,
            'is_synced' => false,
        ];
    }
}
