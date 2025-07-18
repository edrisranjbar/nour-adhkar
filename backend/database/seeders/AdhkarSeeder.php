<?php

namespace Database\Seeders;

use App\Models\Adhkar;
use App\Models\Collection;
use Illuminate\Database\Seeder;

class AdhkarSeeder extends Seeder
{
    public function run(): void
    {
        // Get all collections
        $collections = Collection::all()->keyBy('name');

        // IMPORTANT: All of these methods need to be expanded to include ALL adhkar from 
        // the corresponding JavaScript files (src/assets/js/collections/*.js)
        // The current implementation only includes a few examples from each collection
        
        // Daily Adhkar
        $this->seedDailyAdhkar($collections['اذکار روزانه']->id);
        
        // Morning Adhkar - Must include ALL adhkar from morning.js (currently ~30 items)
        $this->seedMorningAdhkar($collections['اذکار صبحگاه']->id);
        
        // Night Adhkar - Must include ALL adhkar from night.js (currently ~30 items)
        $this->seedNightAdhkar($collections['اذکار شامگاه']->id);
        
        // Ramadan Adhkar - Must include ALL adhkar from ramadan.js
        $this->seedRamadanAdhkar($collections['اذکار ماه رمضان']->id);
        
        // Istikhara Prayer - Must include ALL adhkar from istikhara.js
        $this->seedIstikharaAdhkar($collections['دعاء استخاره']->id);
    }
    
    private function seedDailyAdhkar($collectionId): void
    {
        $adhkar = [
            [
                'title' => 'سبحان الله',
                'prefix' => '',
                'arabic_text' => 'سبحان الله',
                'translation' => 'پاک و منزه است الله',
                'count' => 33,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'الحمد لله',
                'prefix' => '',
                'arabic_text' => 'الحمد لله',
                'translation' => 'ستایش مخصوص الله است',
                'count' => 33,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'الله أكبر',
                'prefix' => '',
                'arabic_text' => 'الله أكبر',
                'translation' => 'الله بزرگ‌تر است',
                'count' => 33,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'لا إله إلا الله',
                'prefix' => '',
                'arabic_text' => 'لا إله إلا الله وحده لا شريك له، له الملك وله الحمد وهو على كل شيء قدير',
                'translation' => 'هیچ معبودی جز الله نیست، یکتاست و شریکی ندارد، پادشاهی و ستایش از آن اوست و او بر هر چیزی تواناست',
                'count' => 100,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'أستغفر الله',
                'prefix' => '',
                'arabic_text' => 'أستغفر الله',
                'translation' => 'از الله آمرزش می‌طلبم',
                'count' => 100,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'صلوات',
                'prefix' => '',
                'arabic_text' => 'اللهم صل على محمد وعلى آل محمد',
                'translation' => 'خدایا بر محمد و خاندان محمد درود فرست',
                'count' => 100,
                'collection_id' => $collectionId
            ]
        ];
        
        foreach ($adhkar as $dhikr) {
            Adhkar::create($dhikr);
        }
    }
    
    private function seedMorningAdhkar($collectionId): void
    {
        $adhkar = [
            [
                'title' => 'آیة الکرسی',
                'prefix' => 'أَعُوذُ بِاللهِ مِنْ الشَّيْطَانِ الرَّجِيمِ',
                'arabic_text' => 'اللّهُ لاَ إِلَـهَ إِلاَّ هُوَ الْحَيُّ الْقَيُّومُ لاَ تَأْخُذُهُ سِنَةٌ وَلاَ نَوْمٌ لَّهُ مَا فِي السَّمَاوَاتِ وَمَا فِي الأَرْضِ مَن ذَا الَّذِي يَشْفَعُ عِنْدَهُ إِلاَّ بِإِذْنِهِ يَعْلَمُ مَا بَيْنَ أَيْدِيهِمْ وَمَا خَلْفَهُمْ وَلاَ يُحِيطُونَ بِشَيْءٍ مِّنْ عِلْمِهِ إِلاَّ بِمَا شَاء وَسِعَ كُرْسِيُّهُ السَّمَاوَاتِ وَالأَرْضَ وَلاَ يَؤُودُهُ حِفْظُهُمَا وَهُوَ الْعَلِيُّ الْعَظِيمُ.',
                'translation' => 'هیچ ﻣﻌﺒﻮدی ﺑﺮ ﺣﻖ ﺟﺰ او ﻧﯿﺴﺖ. زﻧﺪه ی پایدار و نگه دارﻧﺪه ی ﻋﺎﻟﻢ اﺳﺖ، ﻧﻪ چُرت او را ﻓﺮا می‌گیرد و ﻧﻪ ﺧﻮاب، آنچه در آﺳﻤﺎن ﻫﺎ و آنچه در زﻣﯿﻦ اﺳﺖ از آن اوﺳﺖ، کیست که در ﻧﺰد او ﺟﺰ ﺑﻪ ﻓﺮﻣﺎن او ﺷﻔﺎﻋﺖ کند؟ آﻧچه در پیش روی آﻧﺎن و آنچه در پشت ﺳﺮﺷﺎن اﺳﺖ می‌داند. و کسی ﺑﻠﻨﺪ و ﻣﺘﻌﺎلی (ﺑﺮ ﻋﺮش) و ﺑﺰرگ اﺳﺖ.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'سوره ی اخلاص',
                'prefix' => 'بِسْمِ اللهِ الرَّحْمنِ الرَّحِيم',
                'arabic_text' => 'قُلْ هُوَ ٱللَّهُ أَحَدٌ، ٱللَّهُ ٱلصَّمَدُ، لَمْ یَلِدْ وَلَمْ یُولَدْ، وَلَمْ يَكُن لَّهُۥ كُفُوًا أَحَدٌۢ',
                'translation' => '‏بگو : خدا ، يگانه يكتا است .‏ ‏خدا ، سَرورِ والاي برآورنده اميدها و برطرف كننده نيازمنديها است .‏ ‏نزاده است و زاده نشده است .‏ ‏و كسي همتا و همگون او نمیباشد .‏',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'سوره ی فلق',
                'prefix' => 'بِسْمِ اللهِ الرَّحْمنِ الرَّحِيم',
                'arabic_text' => 'قُلْ أَعُوذُ بِرَبِّ ٱلْفَلَقِ، مِن شَرِّ مَا خَلَقَ، وَمِن شَرِّ غَاسِقٍ إِذَا وَقَبَ، وَمِن شَرِّ ٱلنَّفَّٰثَٰتِ فِى ٱلْعُقَدِ، وَمِن شَرِّ حَاسِدٍ إِذَا حَسَدَ',
                'translation' => '‏بگو : پناه میبرم به خداوندگار سپيده‌دم .‏ ‏از شر هر آنچه خداوند آفريده است .‏ ‏و از شرّ شب بدان گاه كه كاملاً فرا میرسد ( و جهان را به زير تاريكي خود میگيرد ) .‏ ‏و از شرّ حسود بدان گاه كه حسد میورزد .‏',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'سوره ی ناس',
                'prefix' => 'بِسْمِ اللهِ الرَّحْمنِ الرَّحِيم',
                'arabic_text' => 'قُلْ أَعُوذُ بِرَبِّ ٱلنَّاسِ، مَلِكِ ٱلنَّاسِ، إِلَٰهِ ٱلنَّاسِ، مِن شَرِّ ٱلْوَسْوَاسِ ٱلْخَنَّاسِ، ٱلَّذِى یُوَسْوِسُ فِى صُدُورِ ٱلنَّاسِ، مِنَ ٱلْجِنَّةِ وَٱلنَّاسِ',
                'translation' => '‏بگو : پناه می‌برم به پروردگار مردمان‌‏ ‏به مالك و حاكم ( واقعی ) مردمان .‏ ‏به معبود ( به حقّ ) مردمان .‏ ‏از شر وسوسه‌گری كه واپس می‌رود ( اگر برای چیره‌شدن بر او ، از خدا كمك بخواهی و خویشتن را در پناهش داری ) .‏ ‏وسوسه‌گری است كه در سینه‌های مردمان به وسوسه می‌پردازد ( و ایشان را به سوی زشتی و گناه و ترك خوبیها و واجبات می‌خواند ) .‏ ‏( در سینه‌های مردمانی ) از جنّیها و انسانها .‏',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'أَصْـبَحْنا وَأَصْـبَحَ المُـلْكُ لله وَالحَمدُ لله ، لا إلهَ إلاّ اللّهُ وَحدَهُ لا شَریكَ لهُ، لهُ المُـلكُ ولهُ الحَمْـد، وهُوَ على كلّ شَیءٍ قدیر ، رَبِّ أسْـأَلُـكَ خَـیرَ ما فی هـذا الیوم وَخَـیرَ ما بَعْـدَه ، وَأَعـوذُ بِكَ مِنْ شَـرِّ ما فی هـذا الیوم وَشَرِّ ما بَعْـدَه',
                'translation' => 'ما و تمام جهانیان، شب را براى خدا به صبح رسانیدیم، و حمد از آن خداست، هیچ معبودى، بجز الله که یکتاست و شریکى ندارد وجود ندارد. پادشاهى و حمد فقط از آن اوست و او بر هر چیز قادر است. الهى! من خیر آنچه در این روز است و خیر آنچه بعد از آن است را از از تو مىطلبم، و از شرّ آنچه که در این روز و ما بعد آن، وجود دارد، به تو پناه می‌ىبرم. الهى! من از تنبلى و بدی‏هاى پیرى به تو پناه می‌ىبرم، بار الها! من از عذاب آتش و قبر به تو پناه مى برم.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللّهـمَّ أَنْتَ رَبِّـی لا إلهَ إلاّ أَنْتَ ، خَلَقْتَنـی وَأَنا عَبْـدُك ، وَأَنا عَلـى عَهْـدِكَ وَوَعْـدِكَ ما اسْتَـطَعْـت ، أَعـوذُبِكَ مِنْ شَـرِّ ما صَنَـعْت ، أَبـوءُ لَـكَ بِنِعْـمَتِـكَ عَلَـیَّ وَأَبـوءُ بِذَنْـبی فَاغْفـِرْ لی فَإِنَّـهُ لا یَغْـفِرُ الذُّنـوبَ إِلاّ أَنْتَ',
                'translation' => 'الهى! تو پروردگار من هستى، بجز تو معبود دیگرى نیست، تو مرا آفریدى، و من بنده ‏ى تو هستم، و بر پیمان و عده‏ام با تو بر حسب استطاعت خود، پایبند هستم، و از شر آنچه که انجام داده‏ام به تو پناه می‌برم، به نعمتى که به من عطا فرموده ‏اى، اعتراف می‌کنم، و به گناهم اقرار می‌نمایم، پس مرا ببخشاى، چرا که بجز تو کسى گناهان را نمى‌ بخشاید',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'رَضیـتُ بِاللهِ رَبَّـاً وَبِالإسْلامِ دیـناً وَبِمُحَـمَّدٍ صلى الله علیه وسلم نَبِیّـاً',
                'translation' => 'به ربوبیت الله، و به داشتن دین اسلام، و پیامبرى محمد –صلى الله علیه وسلم- راضى و خشنود هستم',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللّهُـمَّ إِنِّـی أَصْبَـحْتُ أُشْـهِدُك ، وَأُشْـهِدُ حَمَلَـةَ عَـرْشِـك ، وَمَلَائِكَتَكَ ، وَجَمـیعَ خَلْـقِك ، أَنَّـكَ أَنْـتَ اللهُ لا إلهَ إلاّ أَنْـتَ وَحْـدَكَ لا شَریكَ لَـك ، وَأَنَّ ُ مُحَمّـداً عَبْـدُكَ وَرَسـولُـك',
                'translation' => 'الهى! من در این صبح گاه، تو را و حاملان عرش و تمام فرشتگانت و کلیّه مخلوقات تو را گواه می‌گیرم بر این که تو الله هستى، بجز تو معبود دیگرى «بحق» وجود ندارد، تو یگانه‏اى و شریکى ندارى، و محمد –صلى الله علیه وسلم- بنده و فرستاده‏ ى تو است',
                'count' => 4,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللّهُـمَّ ما أَصْبَـَحَ بی مِـنْ نِعْـمَةٍ أَو بِأَحَـدٍ مِـنْ خَلْـقِك ، فَمِـنْكَ وَحْـدَكَ لا شریكَ لَـك ، فَلَـكَ الْحَمْدُ وَلَـكَ الشُّكْـر',
                'translation' => 'الهى! هر نعمتى که در این صبح، شامل حال من یا یکى از مخلوقات شده، از طرف تو بوده است، تو شریکى ندارى، پس ستایش و شکر از آنِ تو است.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'حَسْبِـیَ اللّهُ لا إلهَ إلاّ هُوَ عَلَـیهِ تَوَكَّـلتُ وَهُوَ رَبُّ العَرْشِ العَظـیم',
                'translation' => 'الله براى من کافى است، بجز او معبود دیگرى «بحق» نیست، بر تو توکّل کردم و او پروردگار عرش بزرگ است',
                'count' => 7,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'بِسـمِ اللهِ الذی لا یَضُـرُّ مَعَ اسمِـهِ شَیءٌ فی الأرْضِ وَلا فی السّمـاءِ وَهـوَ السّمـیعُ العَلـیم',
                'translation' => 'به نام خدایى که با نام وى هیچ چیز در زمین و آسمان، گزندى نمی‌رساند، و او شنوا و دانا است',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'أَصْبَـحْـنا عَلَى فِطْرَةِ الإسْلاَمِ، وَعَلَى كَلِمَةِ الإِخْلاَصِ، وَعَلَى دِینِ نَبِیِّنَا مُحَمَّدٍ صَلَّى اللهُ عَلَیْهِ وَسَلَّمَ، وَعَلَى مِلَّةِ أَبِینَا إبْرَاهِیمَ حَنِیفاً مُسْلِماً وَمَا كَانَ مِنَ المُشْرِكِینَ',
                'translation' => 'ما بر فطرت اسلام، کلمه‏ ى اخلاص، دین پیامبرمان محمد –صلى الله علیه وسلم- و آئین پدرمان ابراهیم؛ صبح کردیم، همان ابراهیمی‌که فقط به سوى حقّ، تمایل داشت و فرمان‌بردار خداوند بود، و از مشرکان نبود',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'سُبْحـانَ اللهِ وَبِحَمْـدِهِ عَدَدَ خَلْـقِه ، وَرِضـا نَفْسِـه ، وَزِنَـةَ عَرْشِه ، وَمِـدادَ كَلِمـاتِـه',
                'translation' => 'تسبیح و پاکى الله و ستایش او را به تعداد آفریدگانش خشنود‏ىاش و سنگینى عرشش و جوهر سخنانش، بیان می‌نمایم.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللّهُـمَّ عافِـنی فی بَدَنـی ، اللّهُـمَّ عافِـنی فی سَمْـعی ، اللّهُـمَّ عافِـنی فی بَصَـری ، لا إلهَ إلاّ أَنْـتَ',
                'translation' => 'بار الها! در بدنم عافیت ده، بار الها! در گوشم عافیت ده، خدایا! در چشمم عافیت ده، بجز تو معبود دیگرى «بحق» وجود ندارد، از کفر به تو پناه می‌برم، از فقر به تو پناه می‌برم، از عذاب قبر به تو پناه می‌برم، بجز تو معبود دیگرى «بحق» وجود ندارد.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللّهُـمَّ إِنّـی أَعـوذُ بِكَ مِنَ الْكُـفر ، وَالفَـقْر ، وَأَعـوذُ بِكَ مِنْ عَذابِ القَـبْر ، لا إلهَ إلاّ أَنْـتَ',
                'translation' => 'بار الها! در بدنم عافیت ده، بار الها! در گوشم عافیت ده، خدایا! در چشمم عافیت ده، بجز تو معبود دیگرى «بحق» وجود ندارد، از کفر به تو پناه می‌برم، از فقر به تو پناه می‌برم، از عذاب قبر به تو پناه می‌برم، بجز تو معبود دیگرى «بحق» وجود ندارد.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللّهُـمَّ إِنِّـی أسْـأَلُـكَ العَـفْوَ وَالعـافِـیةَ فی الدُّنْـیا وَالآخِـرَة ، اللّهُـمَّ إِنِّـی أسْـأَلُـكَ العَـفْوَ وَالعـافِـیةَ فی دینی وَدُنْـیایَ وَأهْـلی وَمالـی ، اللّهُـمَّ اسْتُـرْ عـوْراتی وَآمِـنْ رَوْعاتـی ، اللّهُـمَّ احْفَظْـنی مِن بَـینِ یَدَیَّ وَمِن خَلْفـی وَعَن یَمـینی وَعَن شِمـالی ، وَمِن فَوْقـی ، وَأَعـوذُ بِعَظَمَـتِكَ أَن أُغْـتالَ مِن تَحْتـی',
                'translation' => 'الهى! عفـو و عافیت دنـیا و آخرت را از تـو می‌خواهم. بار الها! عفو و عافیت دین، دنیا، خانواده و مالم را از تو مسألت مىنمایم. بار الها! عیوب مرا بپوشان و ترس مرا به ایمنی مبدّل ساز. الهى! مرا از جلو، پشت سر، سمت راست و چپ و بالاى سرم، محافظت بفرما، و به بزرگى و عظمت تو پناه می‌برم از اینکه ناگهان از زیر پایم نابود شوم.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'یَا حَیُّ يَا قیُّومُ بِرَحْمَتِكَ أَسْتَغِيثُ أَصْلِحْ لِي شَأْنِي كُلَّهُ وَلَا تَكِلْنِي إِلَى نَفْسِي طَرْفَةَ عَيْنٍ',
                'translation' => 'اى زنده و پا بـرجا! بـه وسیله‏ ى رحمت تـو از تـو کمک می‌خواهم، همه‏ ى امورم را اصلاح بفرما، و مرا به اندازه‏ ى یک چشم به هم زدن به خود رها مکن',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'أَصْبَـحْـنا وَأَصْبَـحْ المُـلكُ للهِ رَبِّ العـالَمـین ، اللّهُـمَّ إِنِّـی أسْـأَلُـكَ خَـيرَ هـذا الـیَوْم ، فَـتْحَهُ ، وَنَصْـرَهُ ، وَنـورَهُ وَبَـرَكَتَـهُ ، وَهُـداهُ ، وَأَعـوذُ بِـكَ مِـنْ شَـرِّ ما فـیهِ وَشَـرِّ ما بَعْـدَه',
                'translation' => 'ما و تمام جهانیان، شب را به صبح رسانیدیم براى خدایى که پروردگار جهانیان است. بار الها! من از تو خوبى امروز، یعنى فتح، پیروزى، نور، برکت و هدایتش را مسألت می‌نمایم از بدى آنچه ‏امروز و بعد از آن، پیش می‌آید، به تو پناه می‌برم',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللّهُـمَّ عالِـمَ الغَـیْبِ وَالشّـهادَةِ فاطِـرَ السّماواتِ وَالْأَرْضِ، رَبَّ كُلِّ شَيْءٍ وَمَلِيكَهُ، أَشْهَدُ أَنْ لَا إِلَهَ إِلَّا أَنْتَ، أَعُوذُ بِكَ مِنْ شَرِّ نَفْسِي، وَمِنْ شَرِّ الشَّيْطَانِ وَشِرْكِهِ، وَأَنْ أَقْتَرِفَ عَلَى نَفْسِي سُوءًا، أَوْ أَجُرَّهُ إِلَى مُسْلِمٍ',
                'translation' => 'بار الها! ای دانای پنهان و آشکار، ای پدیدآورنده آسمان‌ها و زمین، ای پروردگار و فرمانروای هر چیز، من گواهی می‌دهم که معبودی جز تو نیست. از شر نفس خود و از شر شیطان و شرک او به تو پناه می‌برم، و از اینکه علیه خود بدی انجام دهم یا آن را به مسلمانی برسانم.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'أَعـوذُ بِكَلِمَاتِ اللَّهِ التَّامَّاتِ مِنْ شَرِّ مَا خَلَقَ',
                'translation' => 'پناه می‌برم به کلمات کامل خدا از شر آنچه آفریده است.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ صَلِّ وَسَلِّمْ وَبَارِكْ على نَبِیِّنَا مُحمَّد',
                'translation' => 'بار الها! بر پیامبرمان محمد –صلى الله علیه وسلم- سلام و درود بفرست',
                'count' => 10,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ إِنَّا نَعُوذُ بِكَ مِنْ أَنْ نُشْرِكَ بِكَ شَیْئًا نَعْلَمُهُ ، وَنَسْتَغْفِرُكَ لِمَا لَا نَعْلَمُهُ',
                'translation' => 'خدایا! به تو پناه می‌بریم از اینکه آگاهانه چیزی را شریک تو قرار دهیم، و از تو آمرزش می‌طلبیم برای آنچه ناآگاهانه شریک تو قرار می‌دهیم.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ إِنِّی أَعُوذُ بِكَ مِنْ الْهَمِّ وَالْحَزَنِ، وَأَعُوذُ بِكَ مِنْ الْعَجْزِ وَالْكَسَلِ، وَأَعُوذُ بِكَ مِنْ الْجُبْنِ وَالْبُخْلِ، وَأَعُوذُ بِكَ مِنْ غَلَبَةِ الدَّیْنِ، وَقَهْرِ الرِّجَالِ',
                'translation' => 'خدایا! از غم و اندوه به تو پناه می‌برم، و از ناتوانی و تنبلی به تو پناه می‌برم، و از ترس و بخل به تو پناه می‌برم، و از غلبه بدهی و فشار مردمان به تو پناه می‌برم.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'أسْتَغْفِرُ اللهَ العَظِیمَ الَّذِی لاَ إلَهَ إلاَّ هُوَ، الحَیُّ القَیُّومُ، وَأتُوبُ إلَیهِ',
                'translation' => 'طلب آمرزش می‌کنم از خدای بزرگ که هیچ معبودی جز او نیست، زنده و پاینده است، و به سوی او بازمی‌گردم.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'يَا رَبِّ , لَكَ الْحَمْدُ كَمَا یَنْبَغِی لِجَلَالِ وَجْهِكَ , وَلِعَظِیمِ سُلْطَانِكَ',
                'translation' => 'ای پروردگار من، ستایش برای توست، آن‌گونه که شایسته جلال و عظمت ذات تو و بزرگی فرمانروایی توست.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ إِنِّی أَسْأَلُكَ عِلْمًا نَافِعًا، وَرِزْقًا طَیِّبًا، وَعَمَلًا مُتَقَبَّلًا',
                'translation' => 'خدایا! از تو دانش سودمند، روزی پاکیزه و کردار پذیرفته شده را خواستارم.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ أَنْتَ رَبِّی لا إِلَهَ إِلَّا أَنْتَ، عَلَیْكَ تَوَكَّلْتُ، وَأَنْتَ رَبُّ الْعَرْشِ الْعَظِیمِ، مَا شَاءَ اللَّهُ كَانَ، وَمَا لَمْ یَشَأْ لَمْ یَكُنْ، وَلا حَوْلَ وَلا قُوَّةَ إِلا بِاللَّهِ الْعَلِیِّ الْعَظِیمِ',
                'translation' => 'خدایا! تو پروردگار منی، هیچ معبودی جز تو نیست، بر تو توکل کرده‌ام و تو پروردگار عرش عظیمی، آنچه خدا بخواهد همان می‌شود و آنچه نخواهد نمی‌شود، و هیچ حرکت و قدرتی جز به [اراده] خدای والامرتبه بزرگ نیست.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ',
                'translation' => 'هیچ معبودی [به حق] جز الله نیست، یکتاست و شریکی ندارد، پادشاهی و ستایش از آنِ اوست، و او بر هر چیزی تواناست.',
                'count' => 100,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ',
                'translation' => 'پاک و منزه است خداوند و ستایش از آن اوست.',
                'count' => 100,
                'collection_id' => $collectionId
            ]
        ];
        
        foreach ($adhkar as $dhikr) {
            Adhkar::create($dhikr);
        }
    }
    
    private function seedNightAdhkar($collectionId): void
    {
        $adhkar = [
            [
                'title' => 'آیة الکرسی',
                'prefix' => 'أَعُوذُ بِاللهِ مِنْ الشَّيْطَانِ الرَّجِيمِ',
                'arabic_text' => 'اللّهُ لاَ إِلَـهَ إِلاَّ هُوَ الْحَيُّ الْقَيُّومُ لاَ تَأْخُذُهُ سِنَةٌ وَلاَ نَوْمٌ لَّهُ مَا فِي السَّمَاوَاتِ وَمَا فِي الأَرْضِ مَن ذَا الَّذِي يَشْفَعُ عِنْدَهُ إِلاَّ بِإِذْنِهِ يَعْلَمُ مَا بَيْنَ أَيْدِيهِمْ وَمَا خَلْفَهُمْ وَلاَ يُحِيطُونَ بِشَيْءٍ مِّنْ عِلْمِهِ إِلاَّ بِمَا شَاء وَسِعَ كُرْسِيُّهُ السَّمَاوَاتِ وَالأَرْضَ وَلاَ يَؤُودُهُ حِفْظُهُمَا وَهُوَ الْعَلِيُّ الْعَظِيمُ.',
                'translation' => 'هیچ ﻣﻌﺒﻮدی ﺑﺮ ﺣﻖ ﺟﺰ او ﻧﯿﺴﺖ. زﻧﺪه ی پایدار و نگه دارﻧﺪه ی ﻋﺎﻟﻢ اﺳﺖ، ﻧﻪ چُرت او را ﻓﺮا می‌گیرد و ﻧﻪ ﺧﻮاب، آنچه در آﺳﻤﺎن ﻫﺎ و آنچه در زﻣﯿﻦ اﺳﺖ از آن اوﺳﺖ، کیست که در ﻧﺰد او ﺟﺰ ﺑﻪ ﻓﺮﻣﺎن او ﺷﻔﺎﻋﺖ کند؟ آﻧچه در پیش روی آﻧﺎن و آنچه در پشت ﺳﺮﺷﺎن اﺳﺖ می‌داند. و کسی ﺑﻠﻨﺪ و ﻣﺘﻌﺎلی (ﺑﺮ ﻋﺮش) و ﺑﺰرگ اﺳﺖ.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'سوره اخلاص',
                'prefix' => 'بِسْمِ اللهِ الرَّحْمنِ الرَّحِيم',
                'arabic_text' => 'قُلْ هُوَ ٱللَّهُ أَحَدٌ، ٱللَّهُ ٱلصَّمَدُ، لَمْ یَلِدْ وَلَمْ یُولَدْ، وَلَمْ يَكُن لَّهُۥ كُفُوًا أَحَدٌۢ',
                'translation' => '(ای پیامبر) بگو: او الله یکتا و یگانه است. الله بی نیاز است (و همه نیازمند او هستند). نه (فرزندی) زاده و نه زاده شده است. و هیچ کس همانند و همتای او نبوده و نیست.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'سوره فلق',
                'prefix' => 'بِسْمِ اللهِ الرَّحْمنِ الرَّحِيم',
                'arabic_text' => 'قُلْ أَعُوذُ بِرَبِّ ٱلْفَلَقِ، مِن شَرِّ مَا خَلَقَ، وَمِن شَرِّ غَاسِقٍ إِذَا وَقَبَ، وَمِن شَرِّ ٱلنَّفَّٰثَٰتِ فِى ٱلْعُقَدِ، وَمِن شَرِّ حَاسِدٍ إِذَا حَسَدَ',
                'translation' => '(ای پیامبر) بگو به پروردگار سپیده‌دم پناه می‌برم. از شر تمام آنچه آفریده است. و از شر تاریکی شب، آن‌گاه که همه جا را فراگیرد. و از شر (ساحران) که با افسون در گره ها می‌دمند. و از شر حسود، آن‌گاه که حسد ورزد.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'سوره ناس',
                'prefix' => 'بِسْمِ اللهِ الرَّحْمنِ الرَّحِيم',
                'arabic_text' => 'قُلْ أَعُوذُ بِرَبِّ ٱلنَّاسِ، مَلِكِ ٱلنَّاسِ، إِلَٰهِ ٱلنَّاسِ، مِن شَرِّ ٱلْوَسْوَاسِ ٱلْخَنَّاسِ، ٱلَّذِى یُوَسْوِسُ فِى صُدُورِ ٱلنَّاسِ، مِنَ ٱلْجِنَّةِ وَٱلنَّاسِ',
                'translation' => 'بگو: پناه می‌برم به پروردگار مردمان‌، به مالك و حاكم مردمان، به معبود مردمان، از شر وسوسه‌گری كه واپس می‌رود، وسوسه‌گری كه در سینه‌های مردمان به وسوسه می‌پردازد، از جنّیها و انسانها.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'أَمْسَيْـنا وَأَمْسـى المـلكُ لله وَالحَمدُ لله، لا إلهَ إلاّ اللّهُ وَحدَهُ لا شَريكَ لهُ، لهُ المُـلكُ ولهُ الحَمْـد، وهُوَ على كلّ شَيءٍ قدير، رَبِّ أسْـأَلُـكَ خَـيرَ ما في هـذهِ اللَّـيْلَةِ وَخَـيرَ ما بَعْـدَهـا، وَأَعـوذُ بِكَ مِنْ شَـرِّ ما في هـذهِ اللَّـيْلةِ وَشَرِّ ما بَعْـدَهـا، رَبِّ أَعـوذُبِكَ مِنَ الْكَسَـلِ وَسـوءِ الْكِـبَر، رَبِّ أَعـوذُ بِكَ مِنْ عَـذابٍ في النّـارِ وَعَـذابٍ في القَـبْر',
                'translation' => 'شب کردیم و تمام هستی برای خدا شب کرد، و ستایش از آنِ خداست، هیچ معبودی بجز الله نیست که یکتاست و شریکی ندارد، پادشاهی و ستایش از آنِ اوست و او بر هر چیزی تواناست. پروردگارا! خیر این شب و خیر آنچه بعد از آن است را از تو خواستارم، و از شر این شب و شر آنچه بعد از آن است به تو پناه می‌برم. پروردگارا! از تنبلی و بدی پیری به تو پناه می‌برم. پروردگارا! از عذاب آتش و عذاب قبر به تو پناه می‌برم.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ عَالِمَ الْغَيْبِ وَالشَّهادَةِ فَاطِرَ السَّمَاوَاتِ وَالْأَرْضِ، رَبَّ كُلِّ شَيْءٍ وَمَلِيكَهُ، أَشْهَدُ أَنْ لَا إِلَهَ إِلَّا أَنْتَ، أَعُوذُ بِكَ مِنْ شَرِّ نَفْسِي، وَمِنْ شَرِّ الشَّيْطَانِ وَشِرْكِهِ، وَأَنْ أَقْتَرِفَ عَلَى نَفْسِي سُوءًا، أَوْ أَجُرَّهُ إِلَى مُسْلِمٍ',
                'translation' => 'خدایا! ای دانای پنهان و آشکار، ای پدیدآورنده آسمان‌ها و زمین، ای پروردگار و فرمانروای هر چیز، من گواهی می‌دهم که معبودی جز تو نیست. از شر نفس خود و از شر شیطان و دام فریبش، و از اینکه خود مرتکب کار بدى شوم و یا به مسلمانی برسانم.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ إِنِّي أَمْسَيْتُ أُشْهِدُكَ وَأُشْهِدُ حَمَلَةَ عَرْشِكَ، وَمَلَائِكَتَكَ وَجَمِيعَ خَلْقِكَ، أَنَّكَ أَنْتَ اللَّهُ لَا إِلَهَ إِلَّا أَنْتَ وَحْدَكَ لَا شَرِيكَ لَكَ، وَأَنَّ مُحَمّـداً عَبْدُكَ وَرَسـولُكَ',
                'translation' => 'خدایا! من در این شامگاه تو را گواه می‌گیرم و حاملان عرش و فرشتگانت و تمامی مخلوقاتت را گواه می‌گیرم که همانا تو الله هستی، هیچ معبودی جز تو نیست، یکتایی و شریکی نداری، و اینکه محمد بنده و فرستاده توست.',
                'count' => 4,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ مَا أَمْسَى بِي مِنْ نِعْمَةٍ أَوْ بِأَحَدٍ مِنْ خَلْقِكَ فَمِنْكَ وَحْدَكَ لا شَریكَ لَكَ، فَلَكَ الْحَمْدُ وَلَكَ الشُّكْـر',
                'translation' => 'بار الها! هر نعمتى که در این شامگاه نصیب من یا هر یک از آفریدگانت شده، از جانب توست که یکتایی و شریکی نداری، پس سپاس و شکر تنها برای توست.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'أَعُوذُ بِكَلِمَاتِ اللَّهِ التَّامَّاتِ مِنْ شَرِّ مَا خَلَقَ',
                'translation' => 'پناه می‌برم به کلمات کامل خدا از شر آنچه آفریده است.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ إِنِّي أَسْأَلُكَ الْعَافِيَةَ فِي الدُّنْيَا وَالْآخِرَةِ، اللَّهُمَّ إِنِّي أَسْأَلُكَ الْعَفْوَ وَالْعَافِيَةَ فِي دِينِي وَدُنْيَايَ وَأَهْلِي وَمَالِي، اللَّهُمَّ اسْتُرْ عَوْرَاتِي، وَآمِنْ رَوْعَاتِي، اللَّهُمَّ احْفَظْنِي مِنْ بَيْنِ يَدَيَّ، وَمِنْ خَلْفِي، وَعَنْ يَمِينِي، وَعَنْ شِمَالِي، وَمِنْ فَوْقِي، وَأَعُوذُ بِعَظَمَتِكَ أَنْ أُغْتَالَ مِن تَحْتـی',
                'translation' => 'بار الها! از تو عافیت در دنیا و آخرت را خواستارم. بار الها! از تو عفو و عافیت در دین و دنیا و خانواده و مالم را می‌خواهم. بار الها! عیب‌هایم را بپوشان و ترس‌هایم را به امنیت تبدیل کن. بار الها! مرا از پیش رو، از پشت سر، از سمت راست، از سمت چپ و از بالای سرم حفظ کن، و به عظمت تو پناه می‌برم از اینکه ناگهان از زیر پایم نابود شوم.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ أَنْتَ رَبِّي لَا إِلَهَ إِلَّا أَنْتَ، خَلَقْتَنِي وَأَنَا عَبْدُكَ، وَأَنَا عَلَى عَهْدِكَ وَوَعْدِكَ مَا اسْتَطَعْتُ، أَعُوذُ بِكَ مِنْ شَرِّ مَا صَنَعْتُ، أَبُوءُ لَكَ بِنِعْمَتِكَ عَلَيَّ، وَأَبُوءُ بِذَنْبِي فَاغْفِرْ لِي فَإِنَّهُ لَا يَغْفِرُ الذُّنُوبَ إِلَّا أَنْتَ',
                'translation' => 'بار الها! تو پروردگار منی، هیچ معبودی جز تو نیست، مرا آفریدی و من بنده توام، و بر عهد و پیمانت تا حد توانم پایبندم. از شر آنچه انجام داده‌ام به تو پناه می‌برم، به نعمتت بر خود اقرار می‌کنم، و به گناهم اعتراف می‌کنم، پس مرا بیامرز، زیرا جز تو کسی گناهان را نمی‌آمرزد.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'يَا حَيُّ يَا قَيُّومُ بِرَحْمَتِكَ أَسْتَغِيثُ أَصْلِحْ لِي شَأْنِي كُلَّهُ وَلَا تَكِلْنِي إِلَى نَفْسِي طَرْفَةَ عَيْنٍ',
                'translation' => 'ای زنده پایدار! به رحمت تو فریاد می‌خواهم، همه امورم را اصلاح فرما و مرا به اندازه یک چشم بر هم زدن به خودم وامگذار.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ عَدَدَ خَلْـقِه ، وَرِضَا نَفْسِه ، وَزِنَـةَ عَرْشِه ، وَمِـدادَ كَلِمـاتِـه',
                'translation' => 'پاک و منزه است خداوند و ستایش او را به تعداد آفریدگانش، و به اندازه خشنودی‌اش، و به وزن عرشش، و به مقدار کلماتش.',
                'count' => 3,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ',
                'translation' => 'هیچ معبودی [به حق] جز الله نیست، یکتاست و شریکی ندارد، پادشاهی و ستایش از آنِ اوست، و او بر هر چیزی تواناست.',
                'count' => 100,
                'collection_id' => $collectionId
            ],
            [
                'title' => '',
                'prefix' => '',
                'arabic_text' => 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ',
                'translation' => 'پاک و منزه است خداوند و ستایش از آن اوست.',
                'count' => 100,
                'collection_id' => $collectionId
            ]
        ];
        
        foreach ($adhkar as $dhikr) {
            Adhkar::create($dhikr);
        }
    }
    
    private function seedRamadanAdhkar($collectionId): void
    {
        $adhkar = [
            [
                'title' => 'دعای روزه داران',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ إِنِّي لَكَ صُمْتُ، وَبِكَ آمَنْتُ، وَعَلَيْكَ تَوَكَّلْتُ، وَعَلَى رِزْقِكَ أَفْطَرْتُ',
                'translation' => 'بار الها! برای تو روزه گرفتم، و به تو ایمان آوردم، و بر تو توکل کردم، و با روزی تو افطار نمودم',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'دعای شب قدر',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ إِنَّكَ عَفُوٌّ تُحِبُّ الْعَفْوَ فَاعْفُ عَنِّي',
                'translation' => 'بار الها! تو بخشنده‌ای و بخشش را دوست داری، پس مرا ببخش',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'دعای رؤیت هلال ماه',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ أَهِلَّهُ عَلَيْنَا بِالْأَمْنِ وَالْإِيمَانِ، وَالسَّلَامَةِ وَالْإِسْلَامِ، وَالتَّوْفِيقِ لِمَا تُحِبُّ رَبَّنَا وَتَرْضَى، رَبُّنَا وَرَبُّكَ اللَّهُ',
                'translation' => 'خدایا! این ماه را با امنیت و ایمان، سلامتی و اسلام، و توفیق برای آنچه دوست داری و می‌پسندی، بر ما هلال کن. پروردگار ما و پروردگار تو، الله است.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'دعای استقبال ماه رمضان',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ بَارِكْ لَنَا فِي رَجَبَ وَشَعْبَانَ، وَبَلِّغْنَا رَمَضَانَ',
                'translation' => 'خدایا! ماه رجب و شعبان را برای ما مبارک گردان، و ما را به ماه رمضان برسان.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'دعای سحر',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنْ عَذَابِ جَهَنَّمَ، وَمِنْ عَذَابِ الْقَبْرِ، وَمِنْ فِتْنَةِ الْمَحْيَا وَالْمَمَاتِ، وَمِنْ شَرِّ فِتْنَةِ الْمَسِيحِ الدَّجَّالِ',
                'translation' => 'خدایا! از عذاب جهنم، و از عذاب قبر، و از فتنه‌های زندگی و مرگ، و از شر فتنه مسیح دجال به تو پناه می‌برم.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'دعای افطار',
                'prefix' => '',
                'arabic_text' => 'ذَهَبَ الظَّمَأُ وَابْتَلَّتِ الْعُرُوقُ، وَثَبَتَ الْأَجْرُ إِنْ شَاءَ اللَّهُ',
                'translation' => 'تشنگی رفت و رگ‌ها سیراب شد، و پاداش ثابت شد ان‌شاءالله.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'دعای میانه ماه رمضان',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ اغْفِرْ لِي مَا مَضَى مِنْ ذُنُوبِي، وَاعْصِمْنِي فِيمَا بَقِيَ مِنْ عُمْرِي، وَارْزُقْنِي عَمَلًا زَاكِيًا تَرْضَى بِهِ عَنِّي',
                'translation' => 'خدایا! گناهان گذشته‌ام را بیامرز، و مرا در باقیمانده عمرم نگه دار، و عملی پاک و خالص که به آن از من راضی شوی، روزی‌ام گردان.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'تسبیحات ماه رمضان',
                'prefix' => '',
                'arabic_text' => 'سُبْحَانَ مَنْ تَعَزَّزَ بِالْقُدْرَةِ وَالْبَقَاءِ. سُبْحَانَ مَنْ احْتَجَبَ عَنِ الْأَبْصَارِ. سُبْحَانَ مَنْ تَكَرَّمَ بِالْعِزِّ وَالْوَقَارِ. سُبْحَانَ مَنْ لَا يَنْبَغِي التَّسْبِيحُ إِلَّا لَهُ. سُبْحَانَ ذِي الْفَضْلِ وَالنِّعَمِ. سُبْحَانَ ذِي الْعِزَّةِ وَالْكَرَمِ. سُبْحَانَ ذِي الْجَلَالِ وَالْإِكْرَامِ',
                'translation' => 'پاک و منزه است کسی که با قدرت و بقا عزتمند است. پاک و منزه است کسی که از دیدگان پنهان است. پاک و منزه است کسی که با عزت و وقار گرامی داشته شده است. پاک و منزه است کسی که تسبیح جز برای او شایسته نیست. پاک و منزه است صاحب فضل و نعمت. پاک و منزه است صاحب عزت و کرم. پاک و منزه است صاحب جلال و بزرگواری.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'دعای وداع ماه رمضان',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ لَا تَجْعَلْهُ آخِرَ الْعَهْدِ مِنْ صِيَامِنَا، وَقِيَامِنَا لَكَ، وَاجْعَلْنَا مِنَ الْمَقْبُولِينَ، اللَّهُمَّ تَقَبَّلْ مِنَّا رَمَضَانَ، وَأَعْتِقْ رِقَابَنَا مِنَ النِّيرَانِ',
                'translation' => 'خدایا! این را آخرین عهد از روزه‌داری و عبادت ما برای خودت قرار نده، و ما را از پذیرفته‌شدگان قرار ده. خدایا! ماه رمضان را از ما بپذیر، و ما را از آتش رها کن.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'دعای طلب لیلة القدر',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ إِنَّكَ عَفُوٌّ كَرِيمٌ تُحِبُّ الْعَفْوَ فَاعْفُ عَنِّي',
                'translation' => 'خدایا! تو بخشنده و کریم هستی، بخشش را دوست داری، پس مرا ببخش.',
                'count' => 1,
                'collection_id' => $collectionId
            ],
            [
                'title' => 'دعای قبولی روزه',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ تَقَبَّلْ مِنَّا صِيَامَنَا وَقِيَامَنَا، وَرُكُوعَنَا وَسُجُودَنَا، وَقُعُودَنَا وَتَضَرُّعَنَا، وَتَخَشُّعَنَا وَتَعَبُّدَنَا، وَأَتْمِمْ تَقْصِيرَنَا فِيهِ يَا رَبَّ الْعَالَمِينَ',
                'translation' => 'خدایا! روزه و نمازمان، رکوع و سجودمان، نشستن و تضرع‌مان، خشوع و عبادت‌مان را از ما بپذیر، و کاستی‌هایمان را در آن کامل کن، ای پروردگار جهانیان.',
                'count' => 1,
                'collection_id' => $collectionId
            ]
        ];
        
        foreach ($adhkar as $dhikr) {
            Adhkar::create($dhikr);
        }
    }
    
    private function seedIstikharaAdhkar($collectionId): void
    {
        $adhkar = [
            [
                'title' => 'دعاء استخاره',
                'prefix' => '',
                'arabic_text' => 'اللَّهُمَّ إِنِّي أَسْتَخِيرُكَ بِعِلْمِكَ وَأَسْتَقْدِرُكَ بِقُدْرَتِكَ وَأَسْأَلُكَ مِنْ فَضْلِكَ الْعَظِيمِ فَإِنَّكَ تَقْدِرُ وَلَا أَقْدِرُ وَتَعْلَمُ وَلَا أَعْلَمُ وَأَنْتَ عَلَّامُ الْغُيُوبِ اللَّهُمَّ إِنْ كُنْتَ تَعْلَمُ أَنَّ هَذَا الْأَمْرَ خَيْرٌ لِي فِي دِينِي وَمَعَاشِي وَعَاقِبَةِ أَمْرِي (عَاجِلِ أَمْرِي وَآجِلِهِ) فَاقْدُرْهُ لِي وَيَسِّرْهُ لِي ثُمَّ بَارِكْ لِي فِيهِ وَإِنَّ كُنْتَ تَعْلَمُ أَنَّ هَذَا الْأَمْرَ شَرٌّ لِي فِي دِينِي وَمَعَاشِي وَعَاقِبَةِ أَمْرِي (عَاجِلِ أَمْرِي وَآجِلِهِ) فَاصْرِفْهُ عَنِّي وَاصْرِفْنِي عَنْهُ وَاقْدُرْ لِيَ الْخَيْرَ حَيْثُ كَانَ ثُمَّ أَرْضِنِي (رضِّنِيِ بِه)',
                'translation' => 'بار الها! من به وسیله‌ی علم تو طلب خیر می‌کنم، و به وسیله‌ی قدرت تو طلب قدرت می‌نمایم و از فضل بزرگوارانه‌ی تو مسألت دارم زیرا تو قادری و من قادر نیستم، و تو می‌دانی و من نمی‌دانم و تویی که داننده‌ی غیب‌هایی، بار الها! اگر می‌دانی که این کار در دینم و زندگانی‌ام و سرانجام کارم (کار سریع یا با تأخیرم) به سودم هست مقدّر بگردان برایم و آسان کن و پر برکت فرما؛ و اگر می‌دانی که زیانبار است آن را از من بگردان و مرا از آن، و برایم خیر را هر کجا هست مقدر فرما و آنگاه مرا به آن راضی کن',
                'count' => 1,
                'collection_id' => $collectionId
            ]
        ];
        
        foreach ($adhkar as $dhikr) {
            Adhkar::create($dhikr);
        }
    }
} 