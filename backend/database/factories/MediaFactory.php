<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word . '.' . $this->faker->fileExtension,
            'file_name' => $this->faker->uuid . '.' . $this->faker->fileExtension,
            'mime_type' => $this->faker->mimeType,
            'path' => $this->faker->filePath,
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => $this->faker->numberBetween(1000, 1000000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 