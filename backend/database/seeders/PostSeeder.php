<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user
        $admin = User::where('role', 'admin')->first();
        
        // Get categories
        $categories = Category::all();
        
        $posts = [
            [
                'title' => 'فضیلت اذکار صبح و شام',
                'content' => 'اذکار صبح و شام از مهمترین عباداتی هستند که پیامبر اکرم (ص) بر آن تأکید فراوان داشته‌اند. این اذکار باعث آرامش قلب، دفع بلا و افزایش رزق و روزی می‌شوند.',
                'excerpt' => 'آشنایی با فضایل و اهمیت اذکار صبح و شام در روایات اسلامی',
                'featured_image' => 'https://example.com/images/morning-prayer.jpg',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(2),
                'categories' => ['معارف قرآن', 'خانواده و اجتماع']
            ],
        ];

        foreach ($posts as $postData) {
            $post = Post::create([
                'title' => $postData['title'],
                'content' => $postData['content'],
                'excerpt' => $postData['excerpt'],
                'featured_image' => $postData['featured_image'],
                'status' => $postData['status'],
                'published_at' => $postData['published_at'],
                'user_id' => $admin->id
            ]);

            // Attach categories
            foreach ($postData['categories'] as $categoryName) {
                $category = $categories->firstWhere('name', $categoryName);
                if ($category) {
                    $post->categories()->attach($category->id);
                }
            }
        }

        // Create some draft posts
        $draftPosts = [
            [
                'title' => 'اذکار و دعاهای سفر',
                'content' => 'در سفر، علاوه بر اذکار معمول، دعاها و اذکار خاصی سفارش شده که باعث حفظ مسافر و رفع مشکلات سفر می‌شوند.',
                'excerpt' => 'معرفی اذکار و دعاهای مخصوص سفر',
                'featured_image' => 'https://example.com/images/travel-prayer.jpg',
                'categories' => ['خانواده و اجتماع']
            ],
            [
                'title' => 'دعاهای مخصوص بیماران',
                'content' => 'در روایات اسلامی، دعاها و اذکار خاصی برای شفای بیماران و رفع درد و بیماری سفارش شده است.',
                'excerpt' => 'معرفی دعاها و اذکار مخصوص بیماران',
                'featured_image' => 'https://example.com/images/sick-prayer.jpg',
                'categories' => ['معارف قرآن']
            ]
        ];

        foreach ($draftPosts as $postData) {
            $post = Post::create([
                'title' => $postData['title'],
                'content' => $postData['content'],
                'excerpt' => $postData['excerpt'],
                'featured_image' => $postData['featured_image'],
                'status' => 'draft',
                'user_id' => $admin->id
            ]);

            // Attach categories
            foreach ($postData['categories'] as $categoryName) {
                $category = $categories->firstWhere('name', $categoryName);
                if ($category) {
                    $post->categories()->attach($category->id);
                }
            }
        }
    }
} 