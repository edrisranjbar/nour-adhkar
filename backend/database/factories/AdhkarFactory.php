<?php

namespace Database\Factories;

use App\Models\Adhkar;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdhkarFactory extends Factory
{
    protected $model = Adhkar::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'arabic_text' => 'اللَّهُمَّ صَلِّ عَلَى مُحَمَّدٍ',
            'translation' => $this->faker->paragraph,
            'count' => $this->faker->numberBetween(1, 100),
            'prefix' => $this->faker->optional(0.7)->randomElement(['سبحان الله', 'الحمد لله', 'الله أكبر']),
            'collection_id' => Collection::factory()
        ];
    }
} 