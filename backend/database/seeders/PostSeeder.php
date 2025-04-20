<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Create some categories first
        $categories = Category::factory()->count(3)->create();

        // Create 10 published posts
        Post::factory()
            ->count(10)
            ->state(['status' => 'published'])
            ->create()
            ->each(function ($post) use ($categories) {
                $post->category()->associate($categories->random());
                $post->save();
            });

        // Create 5 draft posts
        Post::factory()
            ->count(5)
            ->state(['status' => 'draft'])
            ->create()
            ->each(function ($post) use ($categories) {
                $post->category()->associate($categories->random());
                $post->save();
            });
    }
} 