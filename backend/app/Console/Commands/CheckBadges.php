<?php

namespace App\Console\Commands;

use App\Models\Badge;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Console\Command;

class CheckBadges extends Command
{
    protected $signature = 'badges:check {email? : The email of the user to check}';
    protected $description = 'Check and display badge information';

    public function handle()
    {
        $this->info('---- Badge Diagnostics ----');
        
        // Check if badges exist
        $this->info('Available Badges:');
        $badges = Badge::all();
        foreach ($badges as $badge) {
            $this->line("- {$badge->id}: {$badge->name}");
        }
        
        $this->newLine();
        
        // Get the user
        $email = $this->argument('email') ?? 'edris.qeshm2@gmail.com';
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }
        
        $this->info("User: {$user->name} (ID: {$user->id})");
        
        // Check user badges
        $userBadges = $user->badges()->get();
        if ($userBadges->isEmpty()) {
            $this->warn('User has no badges');
        } else {
            $this->info('User Badges:');
            foreach ($userBadges as $badge) {
                $this->line("- {$badge->name} (earned at: {$badge->pivot->earned_at})");
            }
        }
        
        $this->newLine();
        
        // Try to assign beginner badge manually
        $this->info('Trying to assign beginner badge manually...');
        $beginnerBadge = Badge::where('name', 'تازه‌کار')->first();
        
        if (!$beginnerBadge) {
            $this->error('Beginner badge not found! Check for encoding issues.');
            return 1;
        }
        
        $this->line("Found beginner badge: ID {$beginnerBadge->id} - {$beginnerBadge->name}");
        
        if ($user->badges()->where('badge_id', $beginnerBadge->id)->exists()) {
            $this->info('User already has the beginner badge.');
        } else {
            $user->badges()->attach($beginnerBadge->id, ['earned_at' => now()]);
            $this->info('Beginner badge assigned successfully!');
        }
        
        return 0;
    }
} 