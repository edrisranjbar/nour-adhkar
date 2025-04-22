<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition()
    {
        $name = $this->faker->word . '.' . $this->faker->fileExtension;
        $path = 'uploads/media/' . $this->faker->uuid . '.' . $this->faker->fileExtension;
        
        return [
            'name' => $name,
            'path' => $path,
            'url' => asset('storage/' . $path),
            'type' => $this->faker->mimeType,
            'size' => $this->faker->numberBetween(1000, 1000000),
            'description' => $this->faker->sentence,
            'tags' => $this->faker->words(3),
        ];
    }
} 