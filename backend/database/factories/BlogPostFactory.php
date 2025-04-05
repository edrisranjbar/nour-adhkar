<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    // Array of Islamic and dhikr-related titles in Persian
    protected $titles = [
        'فضیلت ذکر صلوات در روایات اسلامی',
        'آثار معنوی اذکار صبحگاهی در زندگی مؤمن',
        'بررسی فواید ذکر استغفار در قرآن و سنت',
        'اهمیت اذکار شبانه در آرامش روح و روان',
        'جایگاه ذکر لا اله الا الله در عرفان اسلامی',
        'چگونه با ذکر و دعا، آرامش درونی پیدا کنیم؟',
        'نقش دعا در تقویت ارتباط با خداوند متعال',
        'معرفی بهترین اذکار برای شروع روز با آرامش',
        'تأثیر ذکر و دعا در رفع مشکلات زندگی',
        'راهکارهای عملی برای حفظ و تلاوت اذکار روزانه',
        'تأملی بر اذکار ماه مبارک رمضان',
        'فضیلت اذکار روز جمعه در احادیث',
        'معرفی برترین دعاهای قرآنی و کاربرد آنها',
        'ارتباط ذکر و شکرگزاری با سلامت روان',
        'آداب دعا کردن از منظر اسلام'
    ];

    // Array of blog post content excerpts in Persian
    protected $excerpts = [
        'در این مقاله به بررسی فضیلت و آثار معنوی ذکر در زندگی روزمره مسلمانان می‌پردازیم...',
        'ذکر و دعا یکی از مهمترین ارکان عبادت در اسلام است. در این نوشتار...',
        'آشنایی با اذکار صبحگاهی و شامگاهی و تأثیر آنها در زندگی مؤمنان موضوع این مقاله است...',
        'دعا پلی است میان بنده و خالق. در این مطلب به آثار روحی و معنوی دعا خواهیم پرداخت...',
        'در این مقاله، مجموعه‌ای از مهمترین اذکار اسلامی و فضیلت آنها معرفی می‌شود...'
    ];

    // Persian paragraphs with content about Islamic dhikr
    protected function getPersianParagraphs()
    {
        return [
            '<h2>فضیلت ذکر در اسلام</h2>
            <p>ذکر خداوند متعال در قرآن کریم و احادیث اسلامی از جایگاه ویژه‌ای برخوردار است. خداوند متعال در قرآن کریم می‌فرماید: «أَلَا بِذِكْرِ اللَّهِ تَطْمَئِنُّ الْقُلُوبُ» (آگاه باشید که تنها با یاد خدا دل‌ها آرامش می‌یابد). این آیه شریفه به خوبی نشان می‌دهد که یاد خداوند چگونه می‌تواند آرامش بخش قلب‌های ناآرام باشد.</p>
            <p>در روایات اسلامی نیز بر اهمیت ذکر تأکید شده است. پیامبر اکرم (ص) می‌فرمایند: «مثل کسی که پروردگارش را یاد می‌کند و کسی که یاد نمی‌کند، مانند زنده و مرده است». این حدیث نشان‌دهنده اهمیت ذکر در زنده نگه داشتن روح انسان است.</p>',

            '<h2>انواع اذکار و فضایل آنها</h2>
            <p>در سنت اسلامی، اذکار متنوعی وجود دارد که هر کدام فضیلت خاص خود را دارند. یکی از مهمترین اذکار، ذکر «سبحان الله و بحمده، سبحان الله العظیم» است که پیامبر (ص) درباره آن فرموده‌اند: «دو کلمه که بر زبان سبک، اما در میزان اعمال سنگین و نزد خداوند رحمان محبوب است».</p>
            <p>ذکر «لا حول و لا قوة إلا بالله» یکی دیگر از اذکار مهم است که به عنوان گنجی از گنج‌های بهشت معرفی شده است. این ذکر به انسان یادآوری می‌کند که تمام قدرت و توان از آن خداست و انسان بدون یاری او قادر به انجام هیچ کاری نیست.</p>',

            '<h2>آداب ذکر گفتن</h2>
            <p>برای اینکه ذکر تأثیر معنوی عمیق‌تری داشته باشد، رعایت آدابی ضروری است. مهمترین این آداب، حضور قلب است. ذکری که تنها بر زبان جاری شود و قلب از آن غافل باشد، اثر معنوی کمتری خواهد داشت.</p>
            <p>طهارت ظاهری و باطنی، انتخاب مکان و زمان مناسب، و استمرار در ذکر از دیگر آداب مهم ذکر هستند. همچنین توجه به معنای ذکر و تدبر در آن می‌تواند تأثیر معنوی آن را چندین برابر کند.</p>',

            '<h2>اذکار صبحگاهی و شامگاهی</h2>
            <p>اذکار صبحگاهی و شامگاهی از مهمترین اذکار روزانه هستند که در روایات اسلامی بر آنها تأکید زیادی شده است. این اذکار شامل تسبیحات، استغفار، صلوات و دعاهای خاصی هستند که در ابتدا و انتهای روز خوانده می‌شوند.</p>
            <p>از فواید خواندن اذکار صبحگاهی می‌توان به محافظت از انسان در طول روز، برکت در کارها و آرامش روحی اشاره کرد. همچنین اذکار شامگاهی موجب پاک شدن گناهان روز، آرامش در خواب و دوری از کابوس می‌شوند.</p>',

            '<h2>نقش ذکر در تقویت ایمان</h2>
            <p>یکی از مهمترین کارکردهای ذکر، تقویت ایمان قلبی است. ذکر مداوم باعث می‌شود یاد خدا همواره در قلب انسان زنده بماند و این امر به تقویت باورهای دینی کمک شایانی می‌کند.</p>
            <p>امام صادق (ع) می‌فرمایند: «قلب‌ها زنگار می‌گیرند همان‌طور که آهن زنگار می‌گیرد، و صیقل آن ذکر خداست». این روایت به خوبی نشان می‌دهد که چگونه ذکر می‌تواند زنگار غفلت را از قلب انسان بزداید و آن را جلا دهد.</p>',

            '<h2>ذکر در قرآن کریم</h2>
            <p>قرآن کریم سرشار از آیاتی است که به موضوع ذکر پرداخته‌اند. خداوند متعال در آیه 41 سوره احزاب می‌فرماید: «یَا أَیُّهَا الَّذِینَ آمَنُوا اذْکُرُوا اللَّهَ ذِکْرًا کَثِیرًا» (ای کسانی که ایمان آورده‌اید! خدا را بسیار یاد کنید).</p>
            <p>این آیه و آیات مشابه نشان می‌دهند که ذکر خداوند یکی از مهم‌ترین وظایف مؤمنان است. همچنین خداوند در آیه 152 سوره بقره می‌فرماید: «فَاذْکُرُونِی أَذْکُرْکُمْ» (پس مرا یاد کنید تا شما را یاد کنم). این آیه بیانگر رابطه دوسویه میان ذکر بنده و توجه خاص الهی است.</p>'
        ];
    }

    public function definition()
    {
        // Choose a random admin user
        $users = User::all()->pluck('id')->toArray();
        $userId = count($users) > 0 ? $this->faker->randomElement($users) : 1;

        // Choose a random title
        $title = $this->faker->randomElement($this->titles);

        // Generate a slug from the title (transliterate Persian to English)
        $slug = Str::slug($this->transliteratePersian($title));

        // Choose a random excerpt
        $excerpt = $this->faker->randomElement($this->excerpts);

        // Generate content by combining random paragraphs
        $paragraphs = $this->getPersianParagraphs();
        shuffle($paragraphs);
        $content = implode("\n", array_slice($paragraphs, 0, $this->faker->numberBetween(2, count($paragraphs))));

        // Array of placeholder image URLs related to Islamic themes
        $images = [
            'https://source.unsplash.com/random/800x400/?mosque',
            'https://source.unsplash.com/random/800x400/?quran',
            'https://source.unsplash.com/random/800x400/?prayer',
            'https://source.unsplash.com/random/800x400/?islamic',
            'https://source.unsplash.com/random/800x400/?islam',
            null // Sometimes no image
        ];

        return [
            'title' => $title,
            'slug' => $slug . '-' . $this->faker->unique()->numberBetween(1, 100), // Ensure unique slug
            'content' => $content,
            'excerpt' => $excerpt,
            'image' => $this->faker->randomElement($images),
            'user_id' => $userId,
            'status' => $this->faker->randomElement(['published', 'draft']),
            'published_at' => $this->faker->randomElement(['published', 'published', 'published', 'draft']) === 'published'
                ? $this->faker->dateTimeBetween('-1 year', 'now')
                : null,
            'created_at' => $this->faker->dateTimeBetween('-2 years', '-1 month'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    // Simplified transliteration function for Persian to Latin characters
    // This helps create readable slugs from Persian titles
    protected function transliteratePersian($text)
    {
        $persian = ['آ', 'ا', 'ب', 'پ', 'ت', 'ث', 'ج', 'چ', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'ژ', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ک', 'گ', 'ل', 'م', 'ن', 'و', 'ه', 'ی', ' '];
        $latin = ['a', 'a', 'b', 'p', 't', 's', 'j', 'ch', 'h', 'kh', 'd', 'z', 'r', 'z', 'zh', 's', 'sh', 's', 'z', 't', 'z', 'a', 'gh', 'f', 'q', 'k', 'g', 'l', 'm', 'n', 'v', 'h', 'y', '-'];

        return str_replace($persian, $latin, $text);
    }
}
