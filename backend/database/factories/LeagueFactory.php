<?php

namespace Database\Factories;

use App\Models\League;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\League>
 */
class LeagueFactory extends Factory
{
    protected $model = League::class;

    public function definition(): array
    {
        $min = $this->faker->numberBetween(0, 900);
        $max = $min + $this->faker->numberBetween(50, 500);

        return [
            'name' => $this->faker->unique()->randomElement(['لیگ مبتدی', 'لیگ متوسط', 'لیگ حرفه‌ای', 'لیگ قهرمانان']),
            'description' => $this->faker->sentence(6),
            'min_points' => $min,
            'max_points' => $max,
            'icon' => 'fa-trophy',
            'color' => '#A79277',
        ];
    }
}


