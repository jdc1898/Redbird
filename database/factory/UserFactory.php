<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->firstName().' '.fake()->lastName();

        $email = Str::lower(str_replace(' ', '.', $name)).'@example.com';

        return [
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'tenant_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Customize the factory's behavior after making a model.
     */
    public function configure(): static
    {
        return $this
            ->afterMaking(function (User $user) {
                if ($user->tenant_id && $tenant = Tenant::find($user->tenant_id)) {
                    $domain = Str::slug($tenant->name, '').'.com';
                    [$local] = explode('@', $user->email);
                    $user->email = $local.'@'.$domain;
                }
            });
    }
}
