<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CollectionSeeder::class,
            AdhkarSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            BlogPostSeeder::class,
        ]);
    }
}