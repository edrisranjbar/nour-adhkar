<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Generate a URL-friendly unique slug
     *
     * @param string $title
     * @return string
     */
    private function generateSlug($title)
    {
        // Transliteration map for Persian/Arabic characters
        $persian = ['ا', 'ب', 'پ', 'ت', 'ث', 'ج', 'چ', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'ژ', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ک', 'گ', 'ل', 'م', 'ن', 'و', 'ه', 'ی', ' و ', 'آ', 'ئ'];
        $latin = ['a', 'b', 'p', 't', 's', 'j', 'ch', 'h', 'kh', 'd', 'z', 'r', 'z', 'zh', 's', 'sh', 's', 'z', 't', 'z', 'a', 'gh', 'f', 'q', 'k', 'g', 'l', 'm', 'n', 'v', 'h', 'y', '-o-', 'a', 'e'];
        
        // First, replace Persian characters with Latin equivalents
        $text = str_replace($persian, $latin, $title);
        
        // Then create a slug using Laravel's Str::slug
        $slug = Str::slug($text);
        
        // Add a unique suffix if needed
        $originalSlug = $slug;
        $count = 1;
        
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        
        return $slug;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define categories with their subcategories
        $categories = [
            'احادیث و روایات' => [
                'احادیث قدسی',
                'احادیث نبوی',
                'احادیث ائمه',
                'چهل حدیث'
            ],
            'تفسیر قرآن' => [
                'تفسیر موضوعی',
                'تفسیر ترتیبی',
                'علوم قرآنی',
                'مفاهیم قرآنی'
            ],
            'اخلاق و آداب' => [
                'اخلاق فردی',
                'اخلاق اجتماعی',
                'آداب معاشرت',
                'تربیت اخلاقی'
            ],
            'فقه و احکام' => [
                'عبادات',
                'معاملات',
                'احکام خانواده',
                'استفتائات'
            ],
            'عقاید و کلام' => [
                'توحید',
                'نبوت',
                'امامت',
                'معاد',
                'عدل'
            ],
            'تاریخ اسلام' => [
                'سیره پیامبر',
                'تاریخ صدر اسلام',
                'تاریخ تشیع',
                'تاریخ معاصر'
            ],
            'سیره اهل بیت' => [
                'سیره امام علی (ع)',
                'سیره امام حسین (ع)',
                'سیره امام رضا (ع)',
                'سیره حضرت زهرا (س)'
            ],
            'ادعیه و اذکار' => [
                'دعاهای قرآنی',
                'صحیفه سجادیه',
                'مفاتیح الجنان',
                'اذکار روزانه'
            ],
            'معارف قرآن' => [
                'قصص قرآنی',
                'امثال قرآن',
                'اعجاز قرآن',
                'معارف سور'
            ],
            'خانواده و اجتماع' => [
                'تربیت فرزند',
                'روابط همسران',
                'مهارت‌های زندگی',
                'سبک زندگی اسلامی'
            ]
        ];

        // Create categories and their subcategories
        foreach ($categories as $mainCategory => $subcategories) {
            // Create main category
            $parent = Category::create([
                'name' => $mainCategory,
                'slug' => $this->generateSlug($mainCategory),
                'description' => 'مجموعه مطالب مرتبط با ' . $mainCategory,
                'parent_id' => null
            ]);

            // Create subcategories
            foreach ($subcategories as $subcategory) {
                Category::create([
                    'name' => $subcategory,
                    'slug' => $this->generateSlug($subcategory),
                    'description' => 'مطالب مرتبط با ' . $subcategory,
                    'parent_id' => $parent->id
                ]);
            }
        }
    }
}
