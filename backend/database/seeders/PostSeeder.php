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
            [
                'title' => 'آداب و مستحبات ماه رمضان',
                'content' => 'ماه رمضان ماه مهمانی خدا و زمان مناسبی برای تقرب به درگاه الهی است. در این ماه، علاوه بر روزه، اعمال و اذکار خاصی سفارش شده که انجام آنها ثواب فراوانی دارد.',
                'excerpt' => 'معرفی آداب و مستحبات ماه مبارک رمضان',
                'featured_image' => 'https://example.com/images/ramadan.jpg',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(5),
                'categories' => ['معارف قرآن']
            ],
            [
                'title' => 'نقش اذکار در زندگی روزمره',
                'content' => 'اذکار اسلامی نه تنها یک عبادت محسوب می‌شوند، بلکه تأثیرات مثبت بسیاری در زندگی روزمره ما دارند. از آرامش روانی گرفته تا موفقیت در کار و زندگی.',
                'excerpt' => 'بررسی تأثیرات مثبت اذکار در زندگی روزمره',
                'featured_image' => 'https://example.com/images/daily-life.jpg',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(7),
                'categories' => ['خانواده و اجتماع']
            ],
            [
                'title' => 'دعاهای قرآنی برای رفع مشکلات',
                'content' => 'قرآن کریم سرشار از دعاها و اذکاری است که می‌توانند در رفع مشکلات و سختی‌های زندگی به ما کمک کنند. در این مقاله به معرفی برخی از این دعاها می‌پردازیم.',
                'excerpt' => 'معرفی دعاهای قرآنی مؤثر در رفع مشکلات',
                'featured_image' => 'https://example.com/images/quran-prayers.jpg',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(10),
                'categories' => ['معارف قرآن']
            ],
            [
                'title' => 'آداب و اذکار قبل از خواب',
                'content' => 'خوابیدن با اذکار و دعاهای مأثور از پیامبر اکرم (ص) و ائمه اطهار (ع) باعث آرامش بیشتر و محافظت از شر شیطان می‌شود.',
                'excerpt' => 'معرفی اذکار و دعاهای قبل از خواب',
                'featured_image' => 'https://example.com/images/sleep-prayer.jpg',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(12),
                'categories' => ['خانواده و اجتماع']
            ]
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