<?php

namespace Database\Seeders;

use App\Models\Dhikr;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DhikrSeeder extends Seeder
{
    public function run(User $user)
    {
        $today = Carbon::today();
        
        // Create dhikr records for completed dates
        foreach ($user->completed_dates as $date) {
            // Create 1-3 dhikr records per day
            $count = rand(1, 3);
            
            for ($i = 0; $i < $count; $i++) {
                Dhikr::create([
                    'user_id' => $user->id,
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