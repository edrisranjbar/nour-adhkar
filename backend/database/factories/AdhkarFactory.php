<?php

namespace Database\Factories;

use App\Models\Adhkar;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdhkarFactory extends Factory
{
    protected $model = Adhkar::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'prefix' => $this->faker->optional(0.7)->sentence(2),
            'arabic_text' => $this->faker->paragraph,
            'translation' => $this->faker->paragraph,
            'count' => $this->faker->numberBetween(1, 100),
            'collection_id' => null
        ];
    }
} 