<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDhikr;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserDhikrFactory extends Factory
{
    protected $model = UserDhikr::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'arabic_text' => 'اللَّهُمَّ صَلِّ عَلَى مُحَمَّدٍ',
            'translation' => $this->faker->paragraph(),
            'transliteration' => 'Allahumma salli ala Muhammad',
            'count' => $this->faker->numberBetween(1, 100),
            'user_id' => User::factory(),
            'is_completed' => false,
            'completed_count' => 0
        ];
    }
} 