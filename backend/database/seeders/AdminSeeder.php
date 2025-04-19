<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $adminUsers = [
            [
                'name' => 'ادریس رنجبر',
                'email' => 'edris.qeshm2@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'heart_score' => 0,
                'streak' => 0,
            ],
        ];

        foreach ($adminUsers as $adminData) {
            User::updateOrCreate(
                ['email' => $adminData['email']],
                $adminData
            );
        }
    }
} 