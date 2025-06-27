<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Event ID Example: evt_3RbhntGdrIy2qbMJ0JhLWMqY
        return [
            'event_id' => $this->faker->unique()->regexify('evt_[A-Za-z0-9]{20}'),
            'charge_id' => $this->faker->unique()->regexify('ch_[A-Za-z0-9]{20}'),
            'transaction_id' => $this->faker->unique()->regexify('txn_[A-Za-z0-9]{20}'),
            'invoice_id' => $this->faker->unique()->regexify('inv_[A-Za-z0-9]{20}'),
            'customer_id' => User::inRandomOrder()->first()->stripe_id,
            'payment_method_id' => $this->faker->unique()->regexify('pm_[A-Za-z0-9]{20}'),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'paid' => $this->faker->boolean(),
            'payment_method_type' => 'card',
            'payment_method_details_card_brand' => $this->faker->randomElement(['visa', 'mastercard', 'amex']),
            'payment_method_details_card_last4' => $this->faker->numerify('####'),
            'payment_method_details_card_exp_month' => $this->faker->numberBetween(1, 12),
            'payment_method_details_card_exp_year' => $this->faker->numberBetween(date('Y'), date('Y') + 10),
            'payment_method_details_authorization_code' => $this->faker->regexify('[A-Za-z0-9]{8}'),
            'receipt_url' => $this->faker->url(),
            'status' => $this->faker->randomElement(['succeeded', 'pending', 'failed']),
        ];
    }
}
