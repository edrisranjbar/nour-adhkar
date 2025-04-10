<?php

namespace Database\Factories;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'type' => $this->faker->randomElement(['daily', 'special', 'custom']),
        ];
    }
} 