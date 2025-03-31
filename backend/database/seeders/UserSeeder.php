<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create a test user with streak data
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'heart_score' => 75,
            'streak' => 5,
            'completed_dates' => $this->generateTestDates()
        ]);

        // Create dhikr records for the user
        $this->createDhikrRecords($user);
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