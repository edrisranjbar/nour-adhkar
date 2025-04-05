<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetHeartScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:reset-heart-scores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all users heart scores to zero daily';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting all users heart scores to zero...');

        $updatedCount = User::where('heart_score', '>', 0)
            ->update(['heart_score' => 0]);

        $this->info("{$updatedCount} users heart scores have been reset.");

        return Command::SUCCESS;
    }
} 