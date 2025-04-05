<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Make sure we have at least one user to assign as author
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('Creating a default user for blog posts...');
            $user = User::factory()->create([
                'name' => 'مدیر سایت',
                'email' => 'admin@adhkar.ir',
                'password' => bcrypt('password')
            ]);
        }
        
        $this->command->info('Creating blog posts...');
        
        // Create a mix of published and draft posts
        BlogPost::factory()
            ->count(5)
            ->state(['status' => 'published'])
            ->create();
            
        BlogPost::factory()
            ->count(2)
            ->state(['status' => 'draft'])
            ->create();
            
        // Create one featured post with a specific image and longer content
        BlogPost::factory()->create([
            'title' => 'تأثیر شگفت‌انگیز ذکر در آرامش روح و زندگی',
            'slug' => 'tasir-zekr-dar-aramesh',
            'status' => 'published',
            'image' => 'https://source.unsplash.com/random/800x400/?prayer,peace',
            'published_at' => now()->subDays(3),
            'excerpt' => 'در این مقاله به بررسی تأثیر ذکر و دعا در ایجاد آرامش در زندگی مسلمانان در دنیای پراسترس امروزی می‌پردازیم و راهکارهایی عملی برای بهره‌مندی از این آثار معنوی ارائه می‌کنیم.'
        ]);
        
        $this->command->info('Blog posts created successfully!');
    }
} 