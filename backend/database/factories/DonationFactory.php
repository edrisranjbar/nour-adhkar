<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'completed', 'failed']);
        $paid_at = $status === 'completed' ? $this->faker->dateTimeBetween('-1 month', 'now') : null;

        return [
            'amount' => $this->faker->numberBetween(1000, 1000000),
            'status' => $status,
            'transaction_id' => $this->faker->uuid,
            'reference_id' => $this->faker->uuid,
            'paid_at' => $paid_at,
            'user_id' => User::factory(),
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'card_pan' => $this->faker->creditCardNumber,
            'description' => $this->faker->sentence
        ];
    }

    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'paid_at' => now(),
            ];
        });
    }

    public function pending(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'paid_at' => null,
            ];
        });
    }
} 