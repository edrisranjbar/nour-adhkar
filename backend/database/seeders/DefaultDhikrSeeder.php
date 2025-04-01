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
                'translation' => 'پاک و منزه است الله'
            ],
            [
                'title' => 'الحمد لله',
                'count' => 33,
                'prefix' => '',
                'suffix' => '',
                'translation' => 'ستایش مخصوص الله است'
            ],
            [
                'title' => 'الله أكبر',
                'count' => 33,
                'prefix' => '',
                'suffix' => '',
                'translation' => 'الله بزرگ‌تر است'
            ],
            [
                'title' => 'لا إله إلا الله وحده لا شريك له، له الملك وله الحمد وهو على كل شيء قدير',
                'count' => 100,
                'prefix' => '',
                'suffix' => '',
                'translation' => 'هیچ معبودی جز الله نیست، یکتاست و شریکی ندارد، پادشاهی و ستایش از آن اوست و او بر هر چیزی تواناست'
            ],
            [
                'title' => 'أستغفر الله',
                'count' => 100,
                'prefix' => '',
                'suffix' => '',
                'translation' => 'از الله آمرزش می‌طلبم'
            ],
            [
                'title' => 'اللهم صل على محمد وعلى آل محمد',
                'count' => 100,
                'prefix' => '',
                'suffix' => '',
                'translation' => 'خدایا بر محمد و خاندان محمد درود فرست'
            ]
        ];

        foreach ($defaultDhikrs as $dhikr) {
            DefaultDhikr::create($dhikr);
        }
    }
} 