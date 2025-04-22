<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->sentence();
        
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraphs(rand(3, 7), true),
            'excerpt' => $this->faker->paragraph(),
            'user_id' => User::factory(),
            'status' => 'draft',
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Post $post) {
            // Attach a random category to the post
            $category = Category::factory()->create();
            $post->categories()->attach($category->id);
        });
    }

    public function published(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'published',
                'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }

    public function draft(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
                'published_at' => null,
            ];
        });
    }

    public function withCategory(Category $category): self
    {
        return $this->afterCreating(function (Post $post) use ($category) {
            $post->categories()->attach($category->id);
        });
    }

    public function withUser(User $user): self
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }

    public function withLongContent(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'content' => $this->faker->paragraphs(10, true),
                'excerpt' => $this->faker->paragraphs(2, true),
            ];
        });
    }

    public function withShortContent(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'content' => $this->faker->paragraph(),
                'excerpt' => $this->faker->sentence(),
            ];
        });
    }

    public function recent(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    public function old(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => $this->faker->dateTimeBetween('-1 year', '-6 months'),
            ];
        });
    }
} 