<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin users
        $adminUsers = [
            [
                'name' => 'ادریس رنجبر',
                'email' => 'edris.qeshm2@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'heart_score' => 0,
                'streak' => 0,
            ],
            [
                'name' => 'مریم حسینی',
                'email' => 'superadmin@example.com',
                'password' => bcrypt('super123'),
                'role' => 'admin',
                'heart_score' => 0,
                'streak' => 15,
            ],
            [
                'name' => 'رضا احمدی',
                'email' => 'systemadmin@example.com',
                'password' => bcrypt('system123'),
                'role' => 'admin',
                'heart_score' => 0,
                'streak' => 20,
            ],
        ];

        foreach ($adminUsers as $adminData) {
            $admin = User::updateOrCreate(
                ['email' => $adminData['email']],
                $adminData
            );
            
            if (!$admin->completed_dates) {
                $admin->completed_dates = $this->generateTestDates();
                $admin->save();
                $this->createDhikrRecords($admin);
            }
        }

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
            
            if (!$user->completed_dates) {
                $user->completed_dates = $this->generateTestDates();
                $user->save();
                $this->createDhikrRecords($user);
            }
        }
    }

    private function generateTestDates()
    {
        $dates = [];
        $today = Carbon::today();
        
        // Add dates for the last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            
            // Add some random completion dates
            // This will create a pattern of completed dates
            if (rand(0, 100) < 70) { // 70% chance of completion
                $dates[] = $date->format('Y-m-d');
            }
        }
        
        return $dates;
    }

    private function createDhikrRecords(User $user)
    {
        $today = Carbon::today();
        
        // Create dhikr records for completed dates
        foreach ($user->completed_dates as $date) {
            // Create 1-3 dhikr records per day
            $count = rand(1, 3);
            
            for ($i = 0; $i < $count; $i++) {
                $user->dhikrs()->create([
                    'title' => 'سبحان الله',
                    'count' => rand(10, 100),
                    'completed_at' => Carbon::parse($date)->setTime(rand(0, 23), rand(0, 59)),
                    'created_at' => Carbon::parse($date),
                    'updated_at' => Carbon::parse($date)
                ]);
            }
        }
    }
} 