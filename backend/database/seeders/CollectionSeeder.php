<?php

namespace Database\Seeders;

use App\Models\Collection;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $collections = [
            [
                'name' => 'اذکار روزانه',
                'type' => 'daily',
            ],
            [
                'name' => 'اذکار صبحگاه',
                'type' => 'daily',
            ],
            [
                'name' => 'اذکار شامگاه',
                'type' => 'daily',
            ],
            [
                'name' => 'دعای خواب',
                'type' => 'daily',
            ],
            [
                'name' => 'اذکار ماه رمضان',
                'type' => 'special',
            ],
            [
                'name' => 'دعاء استخاره',
                'type' => 'special',
            ],
        ];

        foreach ($collections as $collection) {
            Collection::create($collection);
        }
    }
} 