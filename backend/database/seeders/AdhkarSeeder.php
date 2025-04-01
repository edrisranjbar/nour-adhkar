<?php

namespace Database\Seeders;

use App\Models\Adhkar;
use App\Models\Collection;
use Illuminate\Database\Seeder;

class AdhkarSeeder extends Seeder
{
    public function run()
    {

        $adhkars = [
            [
                'title' => 'سبحان الله',
                'count' => 33,
                'prefix' => '',
                'arabic_text' => 'سبحان الله',
                'translation' => 'پاک و منزه است الله'
            ],
            [
                'title' => 'الحمد لله',
                'count' => 33,
                'prefix' => '',
                'arabic_text' => 'الحمد لله',
                'translation' => 'ستایش مخصوص الله است'
            ],
            [
                'title' => 'الله أكبر',
                'count' => 33,
                'prefix' => '',
                'arabic_text' => 'الله أكبر',
                'translation' => 'الله بزرگ‌تر است'
            ],
            [
                'title' => 'لا إله إلا الله',
                'count' => 100,
                'prefix' => '',
                'arabic_text' => 'لا إله إلا الله وحده لا شريك له، له الملك وله الحمد وهو على كل شيء قدير',
                'translation' => 'هیچ معبودی جز الله نیست، یکتاست و شریکی ندارد، پادشاهی و ستایش از آن اوست و او بر هر چیزی تواناست'
            ],
            [
                'title' => 'أستغفر الله',
                'count' => 100,
                'prefix' => '',
                'arabic_text' => 'أستغفر الله',
                'translation' => 'از الله آمرزش می‌طلبم'
            ],
            [
                'title' => 'صلوات',
                'count' => 100,
                'prefix' => '',
                'arabic_text' => 'اللهم صل على محمد وعلى آل محمد',
                'translation' => 'خدایا بر محمد و خاندان محمد درود فرست'
            ]
        ];

        foreach ($adhkars as $adhkar) {
           Adhkar::create($adhkar);
        }
    }
} 