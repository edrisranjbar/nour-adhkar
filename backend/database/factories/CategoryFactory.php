<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Category $category) {
            //
        })->afterCreating(function (Category $category) {
            //
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Set Faker to use Persian locale
        $this->faker->locale('fa_IR');
        
        // Persian category names related to Islamic topics
        $categoryNames = [
            'احادیث', 'تفسیر قرآن', 'اخلاق اسلامی', 'فقه', 'عقاید', 
            'تاریخ اسلام', 'سیره اهل بیت', 'ادعیه', 'اذکار', 'معارف قرآن',
            'اخلاق', 'عبادات', 'معاملات', 'خانواده', 'اجتماع',
            'اقتصاد اسلامی', 'سیاست اسلامی', 'علم و دین', 'فلسفه اسلامی', 'عرفان'
        ];
        
        $name = $this->faker->unique()->randomElement($categoryNames);
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->realText(rand(50, 150)),
            'parent_id' => null, // Default to no parent
        ];
    }

    /**
     * Indicate that the category has a parent.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withParent()
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_id' => Category::factory(),
            ];
        });
    }

    /**
     * Indicate that the category has children.
     *
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withChildren($count = 3)
    {
        return $this->afterCreating(function (Category $category) use ($count) {
            Category::factory()->count($count)->create([
                'parent_id' => $category->id,
            ]);
        });
    }
} 