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
                'name' => 'مداوم',
                'description' => '۷ روز پشت سر هم ذکر خواندن',
                'icon' => 'fa-solid fa-calendar-check',
                'color' => '#9C27B0',
                'points_required' => 0,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }
    }
} 