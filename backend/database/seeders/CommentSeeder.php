<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Faker\Factory as Faker;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fa_IR');
        $posts = Post::all();
        $users = User::all();

        // Create 50 comments
        for ($i = 0; $i < 50; $i++) {
            $status = $faker->randomElement(['pending', 'approved', 'rejected']);
            $createdAt = $faker->dateTimeBetween('-3 months', 'now');
            
            Comment::create([
                'post_id' => $posts->random()->id,
                'user_id' => $users->random()->id,
                'parent_id' => $faker->boolean(30) ? Comment::inRandomOrder()->first()?->id : null,
                'content' => $faker->paragraph(2),
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        // Create some nested comments (replies)
        $comments = Comment::whereNull('parent_id')->get();
        foreach ($comments as $comment) {
            if ($faker->boolean(40)) { // 40% chance to have replies
                $replyCount = $faker->numberBetween(1, 3);
                for ($i = 0; $i < $replyCount; $i++) {
                    $createdAt = $faker->dateTimeBetween($comment->created_at, 'now');
                    
                    Comment::create([
                        'post_id' => $comment->post_id,
                        'user_id' => $users->random()->id,
                        'parent_id' => $comment->id,
                        'content' => $faker->paragraph(1),
                        'status' => $faker->randomElement(['pending', 'approved', 'rejected']),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
        }
    }
}