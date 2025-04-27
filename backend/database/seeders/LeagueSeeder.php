<?php

namespace Database\Seeders;

use App\Models\League;
use Illuminate\Database\Seeder;

class LeagueSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing leagues
        League::truncate();

        // Create leagues
        League::create([
            'name' => 'لیگ مبتدی',
            'description' => 'لیگ شروع برای همه کاربران جدید. با انجام فعالیت‌های پایه مانند نمازهای روزانه و ورود منظم پیشرفت کنید.',
            'min_points' => 0,
            'max_points' => 999,
            'icon' => 'fa-seedling',
            'color' => '#4CAF50'
        ]);

        League::create([
            'name' => 'لیگ متوسط',
            'description' => 'برای کاربرانی که مشارکت و فعالیت مداوم نشان داده‌اند. با انجام وظایف و مشارکت در بحث‌های جامعه به این لیگ برسید.',
            'min_points' => 1000,
            'max_points' => 4999,
            'icon' => 'fa-star',
            'color' => '#2196F3'
        ]);

        League::create([
            'name' => 'لیگ پیشرفته',
            'description' => 'بالاترین لیگ برای کاربران بسیار فعال و متعهد. با جمع‌آوری امتیاز از طریق مشارکت گسترده و چالش‌های پیشرفته به این لیگ برسید.',
            'min_points' => 5000,
            'max_points' => 999999,
            'icon' => 'fa-crown',
            'color' => '#FFC107'
        ]);
    }
} 