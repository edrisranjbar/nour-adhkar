<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Create BadgeService instance
        $badgeService = new BadgeService();
        
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
            $user = User::updateOrCreate(
                ['email' => $adminData['email']],
                $adminData
            );
            
            // Initialize badges for the admin user
            $badgeService->initializeBadges($user);
            
            // Check for streak-based badges if applicable
            if ($user->streak >= 7) {
                $badgeService->checkAndAwardBadges($user);
            }
        }
    }
} 