<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
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

        for ($i = 0; $i < 10; $i++) {
            $userData = [
                'name' => $persianNames[$i],
                'email' => 'user' . ($i + 1) . '@example.com',
                'password' => bcrypt('password' . ($i + 1)),
                'role' => 'user',
                'streak' => rand(0, 30),
            ];

            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
