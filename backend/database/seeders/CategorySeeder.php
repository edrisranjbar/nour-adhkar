<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing categories
        Category::truncate();

        // Create main categories (parent categories)
        $mainCategories = [
            'احادیث و روایات',
            'تفسیر قرآن',
            'اخلاق و آداب',
            'فقه و احکام',
            'عقاید و کلام',
            'تاریخ اسلام',
            'سیره اهل بیت',
            'ادعیه و اذکار',
            'معارف قرآن',
            'خانواده و اجتماع'
        ];

        $mainCategoryIds = [];

        // Create main categories
        foreach ($mainCategories as $categoryName) {
            $category = Category::factory()->create([
                'name' => $categoryName,
                'slug' => \Illuminate\Support\Str::slug($categoryName),
                'description' => 'دسته‌بندی ' . $categoryName,
                'parent_id' => null
            ]);

            $mainCategoryIds[] = $category->id;
        }

        // Create subcategories for each main category
        foreach ($mainCategoryIds as $parentId) {
            Category::factory()->count(rand(3, 5))->create([
                'parent_id' => $parentId
            ]);
        }

        // Create some standalone categories
        Category::factory()->count(5)->create();
    }
}
