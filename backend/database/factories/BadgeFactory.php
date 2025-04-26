<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'icon' => 'fa-solid fa-star',
            'color' => '#A79277',
            'points_required' => $this->faker->numberBetween(0, 1000),
        ];
    }

    public function predefined()
    {
        return $this->state(function (array $attributes) {
            $badges = [
                [
                    'name' => 'تازه‌کار',
                    'description' => 'کاربر جدیدی که به تازگی به پلتفرم ملحق شده است',
                    'icon' => 'fa-solid fa-seedling',
                    'color' => '#4CAF50',
                    'points_required' => 0,
                ],
                [
                    'name' => 'مستقر',
                    'description' => 'کاربری که مدتی در پلتفرم فعال بوده است',
                    'icon' => 'fa-solid fa-house',
                    'color' => '#2196F3',
                    'points_required' => 100,
                ],
                [
                    'name' => 'مستمر',
                    'description' => 'کاربری که به صورت مداوم و منظم فعالیت می‌کند',
                    'icon' => 'fa-solid fa-calendar-check',
                    'color' => '#9C27B0',
                    'points_required' => 500,
                ],
                [
                    'name' => 'حامی',
                    'description' => 'کاربری که به دیگران کمک می‌کند',
                    'icon' => 'fa-solid fa-hands-helping',
                    'color' => '#FF9800',
                    'points_required' => 300,
                ],
                [
                    'name' => 'محتوا ساز',
                    'description' => 'کاربری که محتوای اصلی تولید می‌کند',
                    'icon' => 'fa-solid fa-pen-fancy',
                    'color' => '#E91E63',
                    'points_required' => 800,
                ],
                [
                    'name' => 'مشارکت‌کننده',
                    'description' => 'کاربری که در بحث‌ها و فعالیت‌ها مشارکت دارد',
                    'icon' => 'fa-solid fa-comments',
                    'color' => '#00BCD4',
                    'points_required' => 400,
                ],
                [
                    'name' => 'سفیر',
                    'description' => 'کاربری که پلتفرم را به دیگران معرفی می‌کند',
                    'icon' => 'fa-solid fa-bullhorn',
                    'color' => '#FFC107',
                    'points_required' => 600,
                ],
                [
                    'name' => 'کاربر فعال',
                    'description' => 'کاربری که در یک ماه اخیر فعالیت زیادی داشته است',
                    'icon' => 'fa-solid fa-bolt',
                    'color' => '#F44336',
                    'points_required' => 700,
                ],
                [
                    'name' => 'پیشرفت کننده',
                    'description' => 'کاربری که به طور مداوم در حال یادگیری است',
                    'icon' => 'fa-solid fa-chart-line',
                    'color' => '#673AB7',
                    'points_required' => 900,
                ],
                [
                    'name' => 'مدیر',
                    'description' => 'کاربری که در گروه‌ها نقش رهبری دارد',
                    'icon' => 'fa-solid fa-crown',
                    'color' => '#795548',
                    'points_required' => 1000,
                ],
                [
                    'name' => 'فوق‌العاده',
                    'description' => 'کاربر بسیار فعال و با تجربه',
                    'icon' => 'fa-solid fa-award',
                    'color' => '#607D8B',
                    'points_required' => 1500,
                ],
                [
                    'name' => 'دوست‌داشتنی',
                    'description' => 'کاربری که محتوایش مورد توجه دیگران است',
                    'icon' => 'fa-solid fa-heart',
                    'color' => '#E91E63',
                    'points_required' => 1200,
                ],
            ];

            return $this->faker->randomElement($badges);
        });
    }
} 