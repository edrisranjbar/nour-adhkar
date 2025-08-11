<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run()
    {
        $badges = [
            [
                'name' => 'تازه‌کار',
                'description' => 'این نشان به محض ایجاد حساب کاربری به شما اعطا می‌شود',
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
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }
    }
} 