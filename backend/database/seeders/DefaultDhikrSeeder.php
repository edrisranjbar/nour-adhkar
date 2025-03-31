<?php

namespace Database\Seeders;

use App\Models\DefaultDhikr;
use Illuminate\Database\Seeder;

class DefaultDhikrSeeder extends Seeder
{
    public function run()
    {
        $defaultDhikrs = [
            [
                'title' => 'سبحان الله',
                'count' => 33,
                'prefix' => '',
                'suffix' => '',
                'translation' => 'Glory be to Allah'
            ],
            [
                'title' => 'الحمد لله',
                'count' => 33,
                'prefix' => '',
                'suffix' => '',
                'translation' => 'Praise be to Allah'
            ],
            [
                'title' => 'الله أكبر',
                'count' => 33,
                'prefix' => '',
                'suffix' => '',
                'translation' => 'Allah is the Greatest'
            ],
            [
                'title' => 'لا إله إلا الله وحده لا شريك له، له الملك وله الحمد وهو على كل شيء قدير',
                'count' => 100,
                'prefix' => '',
                'suffix' => '',
                'translation' => 'None has the right to be worshipped except Allah, alone, without any partner, to Him belongs the dominion and to Him belongs all praise, and He is over all things competent'
            ],
            [
                'title' => 'أستغفر الله',
                'count' => 100,
                'prefix' => '',
                'suffix' => '',
                'translation' => 'I seek forgiveness from Allah'
            ],
            [
                'title' => 'اللهم صل على محمد وعلى آل محمد',
                'count' => 100,
                'prefix' => '',
                'suffix' => '',
                'translation' => 'O Allah, send prayers upon Muhammad and the family of Muhammad'
            ]
        ];

        foreach ($defaultDhikrs as $dhikr) {
            DefaultDhikr::create($dhikr);
        }
    }
} 