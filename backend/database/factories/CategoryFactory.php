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
        return [
            'name' => $this->faker->word,
            'slug' => $this->faker->slug,
            'description' => $this->faker->sentence,
            'created_at' => now(),
            'updated_at' => now(),
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