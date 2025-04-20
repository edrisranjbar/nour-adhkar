<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Default password for testing
            'remember_token' => Str::random(10),
            'role' => $this->faker->randomElement(['user', 'admin']),
        ];
    }

    public function admin(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'admin',
            ];
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
