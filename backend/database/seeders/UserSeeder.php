<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Persian names for regular users
        $persianNames = [
            'سارا رضایی',
            'محمد کریمی',
            'زهرا محمودی',
            'حسین علیزاده',
            'فاطمه صادقی',
            'امیر حسینی',
            'نرگس محمدی',
            'علی رضایی',
            'مریم کریمی',
            'رضا محمودی'
        ];

        // Create 10 regular users
        for ($i = 0; $i < 10; $i++) {
            $userData = [
                'name' => $persianNames[$i],
                'email' => 'user' . ($i + 1) . '@example.com',
                'password' => bcrypt('password' . ($i + 1)),
                'role' => 'user',
                'heart_score' => rand(0, 100),
                'streak' => rand(0, 30),
            ];
            
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
} 