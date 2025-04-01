<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdhkarSeeder extends Seeder
{
    public function run()
    {
        // Create collections
        $collections = [
            ['name' => 'اذکار صبحگاه', 'type' => 'morning'],
            ['name' => 'اذکار شامگاه', 'type' => 'night'],
            ['name' => 'دعای خواب', 'type' => 'sleep'],
            ['name' => 'دعاء استخاره', 'type' => 'istikhara'],
        ];

        foreach ($collections as $collection) {
            DB::table('collections')->insert([
                'name' => $collection['name'],
                'type' => $collection['type'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Common dhikrs that appear in multiple collections
        $commonDhikrs = [
            [
                'title' => 'آیة الکرسی',
                'prefix' => 'أَعُوذُ بِاللهِ مِنْ الشَّیْطَانِ الرَّجِیمِ',
                'arabic_text' => 'اللّهُ لاَ إِلَـهَ إِلاَّ هُوَ الْحَیُّ الْقَیُّومُ لاَ تَأْخُذُهُ سِنَةٌ وَلاَ نَوْمٌ لَّهُ مَا فِی السَّمَاوَاتِ وَمَا فِی الأَرْضِ مَن ذَا الَّذِی یَشْفَعُ عِنْدَهُ إِلاَّ بِإِذْنِهِ یَعْلَمُ مَا بَیْنَ أَیْدِیهِمْ وَمَا خَلْفَهُمْ وَلاَ یُحِیطُونَ بِشَیْءٍ مِّنْ عِلْمِهِ إِلاَّ بِمَا شَاء وَسِعَ كُرْسِیُّهُ السَّمَاوَاتِ وَالأَرْضَ وَلاَ یَؤُودُهُ حِفْظُهُمَا وَهُوَ الْعَلِیُّ الْعَظِیمُ.',
                'translation' => 'خدائی بجز الله وجود ندارد و او زنده پایدار است. او را نه چرتی و نه خوابی فرا نمی‌گیرد. از آنِ او است آنچه در آسمانها و آنچه در زمین است. کیست آن که در پیشگاه او میانجیگری کند مگر با اجازه او؟',
                'count' => 1,
                'collections' => ['morning', 'night', 'sleep']
            ],
            [
                'title' => 'سوره ی اخلاص',
                'prefix' => 'بِسْمِ اللهِ الرَّحْمنِ الرَّحِیم',
                'arabic_text' => 'قُلْ هُوَ ٱللَّهُ أَحَدٌ، ٱللَّهُ ٱلصَّمَدُ، لَمْ یَلِدْ وَلَمْ یُولَدْ، وَلَمْ یَكُن لَّهُۥ كُفُوًا أَحَدٌۢ',
                'translation' => 'بگو: خدا یکتاست. خدا بی‌نیاز است. نه زاده و نه زاده شده است. و هیچ کس همتای او نیست.',
                'count' => 3,
                'collections' => ['morning', 'night']
            ],
            [
                'title' => 'سوره ی فلق',
                'prefix' => 'بِسْمِ اللهِ الرَّحْمنِ الرَّحِیم',
                'arabic_text' => 'قُلْ أَعُوذُ بِرَبِّ ٱلْفَلَقِ، مِن شَرِّ مَا خَلَقَ، وَمِن شَرِّ غَاسِقٍ إِذَا وَقَبَ، وَمِن شَرِّ ٱلنَّفَّٰثَٰتِ فِى ٱلْعُقَدِ، وَمِن شَرِّ حَاسِدٍ إِذَا حَسَدَ',
                'translation' => 'بگو: به پروردگار سپیده‌دم پناه می‌برم. از شر تمام آنچه آفریده است. و از شر تاریکی شب، آن‌گاه که همه جا را فراگیرد. و از شر افسونگران در گره‌ها. و از شر حسود آنگاه که حسد ورزد.',
                'count' => 3,
                'collections' => ['morning', 'night', 'sleep']
            ],
            [
                'title' => 'سوره ی ناس',
                'prefix' => 'بِسْمِ اللهِ الرَّحْمنِ الرَّحِیم',
                'arabic_text' => 'قُلْ أَعُوذُ بِرَبِّ ٱلنَّاسِ، مَلِكِ ٱلنَّاسِ، إِلَٰهِ ٱلنَّاسِ، مِن شَرِّ ٱلْوَسْوَاسِ ٱلْخَنَّاسِ، ٱلَّذِى یُوَسْوِسُ فِى صُدُورِ ٱلنَّاسِ، مِنَ ٱلْجِنَّةِ وَٱلنَّاسِ',
                'translation' => 'بگو: به پروردگار مردم پناه می‌برم. پادشاه مردم. معبود مردم. از شر وسوسه‌گر نهانی. آن که در سینه‌های مردم وسوسه می‌کند. از جن و انس.',
                'count' => 3,
                'collections' => ['morning', 'night', 'sleep']
            ],
        ];

        // Get collection IDs
        $collectionIds = [];
        foreach ($collections as $collection) {
            $collectionIds[$collection['type']] = DB::table('collections')
                ->where('type', $collection['type'])
                ->value('id');
        }

        // Insert common dhikrs
        foreach ($commonDhikrs as $dhikr) {
            $dhikrId = DB::table('adhkars')->insertGetId([
                'title' => $dhikr['title'],
                'prefix' => $dhikr['prefix'],
                'arabic_text' => $dhikr['arabic_text'],
                'translation' => $dhikr['translation'],
                'count' => $dhikr['count'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create collection_adhkars relationships
            foreach ($dhikr['collections'] as $collectionType) {
                DB::table('collection_adhkars')->insert([
                    'collection_id' => $collectionIds[$collectionType],
                    'adhkar_id' => $dhikrId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Sleep specific dhikrs
        $sleepDhikrs = [
            [
                'title' => 'دعای خواب',
                'prefix' => '',
                'arabic_text' => 'بِسْمِكَ اللّهُمَّ أَمُوتُ وَأَحْيَا',
                'translation' => 'بارالها! به نام تو می‌خوابم و زنده می‌شوم.',
                'count' => 1
            ],
            [
                'title' => 'دعای آرامش',
                'prefix' => '',
                'arabic_text' => 'اللّهُمَّ اجْعَلْ لِي فِي نَوْمِي سَكِينَةً وَهُدًى.',
                'translation' => 'بارالها! در خوابم آرامش و هدایت قرار ده.',
                'count' => 1
            ],
            [
                'title' => 'دعای حفاظت',
                'prefix' => '',
                'arabic_text' => 'اللّهُمَّ احْفَظْنِي فِي نَوْمِي وَاجْعَلْهُ نَوْمًا مُبَارَكًا.',
                'translation' => 'بارالها! مرا در خوابم حفظ کن و آن را خواب مبارکی قرار بده.',
                'count' => 1
            ],
        ];

        foreach ($sleepDhikrs as $dhikr) {
            $dhikrId = DB::table('adhkars')->insertGetId([
                'title' => $dhikr['title'],
                'prefix' => $dhikr['prefix'],
                'arabic_text' => $dhikr['arabic_text'],
                'translation' => $dhikr['translation'],
                'count' => $dhikr['count'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('collection_adhkars')->insert([
                'collection_id' => $collectionIds['sleep'],
                'adhkar_id' => $dhikrId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Istikhara dhikr
        $istikharaDhikr = [
            'title' => 'دعاء استخاره',
            'prefix' => '',
            'arabic_text' => 'اللَّهُمَّ إِنِّي أَسْتَخِيرُكَ بِعِلْمِكَ وَأَسْتَقْدِرُكَ بِقُدْرَتِكَ وَأَسْأَلُكَ مِنْ فَضْلِكَ الْعَظِيمِ فَإِنَّكَ تَقْدِرُ وَلَا أَقْدِرُ وَتَعْلَمُ وَلَا أَعْلَمُ وَأَنْتَ عَلَّامُ الْغُيُوبِ اللَّهُمَّ إِنْ كُنْتَ تَعْلَمُ أَنَّ هَذَا الْأَمْرَ خَيْرٌ لِي فِي دِينِي وَمَعَاشِي وَعَاقِبَةِ أَمْرِي فَاقْدُرْهُ لِي وَيَسِّرْهُ لِي ثُمَّ بَارِكْ لِي فِيهِ وَإِنْ كُنْتَ تَعْلَمُ أَنَّ هَذَا الْأَمْرَ شَرٌّ لِي فِي دِينِي وَمَعَاشِي وَعَاقِبَةِ أَمْرِي فَاصْرِفْهُ عَنِّي وَاصْرِفْنِي عَنْهُ وَاقْدُرْ لِيَ الْخَيْرَ حَيْثُ كَانَ ثُمَّ أَرْضِنِي بِه',
            'translation' => 'بار الها به وسیله علمت از تو طلب خیر می‌کنم و به وسیله قدرتت از تو توانایی می خواهم و از تو فضل و بزرگی ات را مسئلت می نمایم. زیرا تو قادر و توانایی و من توانایی ندارم، و تو می دانی و من نمی دانم و تو به امورات غیب و پنهانی آگاه هستی. خدایا! اگر می دانی این كار برایم خیر است آن را برایم مقدر و آسان كن و در آن برکت بده و اگر می‎دانی این كار برایم شر است آن را از من دور کن و مرا از آن دور کن و خیر را هر كجا هست برایم مقدر کن و مرا به آن راضی کن.',
            'count' => 1
        ];

        $dhikrId = DB::table('adhkars')->insertGetId([
            'title' => $istikharaDhikr['title'],
            'prefix' => $istikharaDhikr['prefix'],
            'arabic_text' => $istikharaDhikr['arabic_text'],
            'translation' => $istikharaDhikr['translation'],
            'count' => $istikharaDhikr['count'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('collection_adhkars')->insert([
            'collection_id' => $collectionIds['istikhara'],
            'adhkar_id' => $dhikrId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
} 