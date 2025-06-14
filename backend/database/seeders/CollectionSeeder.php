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
                'slug' => 'daily',
            ],
            [
                'name' => 'اذکار صبحگاه',
                'type' => 'daily',
                'slug' => 'morning',
            ],
            [
                'name' => 'اذکار شامگاه',
                'type' => 'daily',
                'slug' => 'night',
            ],
            [
                'name' => 'اذکار خواب',
                'type' => 'daily',
                'slug' => 'sleep',
            ],
            [
                'name' => 'اذکار ماه رمضان',
                'type' => 'special',
                'slug' => 'ramadan',
            ],
            [
                'name' => 'دعاء استخاره',
                'type' => 'special',
                'slug' => 'istikhara',
            ],
        ];

        foreach ($collections as $collection) {
            Collection::create($collection);
        }
    }
} 