<template>
  <div class="landing" :data-theme="theme || null" :lang="lang" dir="rtl">
    <a class="skip" href="#main">{{ t.skip }}</a>

    <div class="bar">
      <nav class="wrap nav" :aria-label="t.navLabel">
        <a class="brand" href="#top">
          <img src="@/assets/icons/logo.png" alt="" width="32" height="32" />
          <span>{{ t.name }}</span>
        </a>
        <ul class="links">
          <li><a href="#features">{{ t.navFeatures }}</a></li>
          <li><a href="/adhkar">{{ t.navAdhkar }}</a></li>
          <li><a href="/blog">{{ t.navBlog }}</a></li>
          <li><a href="#privacy">{{ t.navPrivacy }}</a></li>
          <li><a href="#support">{{ t.navSupport }}</a></li>
          <li><a href="#download">{{ t.navDownload }}</a></li>
        </ul>
        <div class="ctrl">
          <div class="seg" role="group" :aria-label="t.langLabel">
            <button type="button" :aria-pressed="lang === 'fa'" @click="setLang('fa')" lang="fa">فا</button>
            <button type="button" :aria-pressed="lang === 'ar'" @click="setLang('ar')" lang="ar">ع</button>
            <button type="button" :aria-pressed="lang === 'ckb'" @click="setLang('ckb')" lang="ckb">کو</button>
          </div>
          <a class="nav-donate" :href="donateUrl" target="_blank" rel="noopener noreferrer" @click="track('donate_click', 'nav')">{{ t.support }}</a>
          <button type="button" class="icon-btn" @click="toggleTheme" :aria-label="t.themeLabel">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z" /></svg>
          </button>
        </div>
      </nav>
    </div>

    <main id="main">
      <div :key="lang" class="swap">
        <!-- Hero -->
        <section id="top" class="hero">
          <svg class="hero-pattern" aria-hidden="true" width="100%" height="100%">
            <defs>
              <pattern id="khatam" width="72" height="72" patternUnits="userSpaceOnUse">
                <g fill="none" stroke="currentColor" stroke-width="1">
                  <rect x="18" y="18" width="36" height="36" />
                  <rect x="18" y="18" width="36" height="36" transform="rotate(45 36 36)" />
                  <circle cx="36" cy="36" r="7" />
                  <path d="M0 0 18 18M72 0 54 18M0 72l18-18M72 72 54 54" />
                </g>
              </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#khatam)" />
          </svg>

          <div class="wrap hero-copy">
            <h1 class="rise" style="--d: 1">
              <span class="w1">{{ t.nameA }}</span>
              <span class="w2">{{ t.nameB }}</span>
            </h1>
            <p class="tagline rise" style="--d: 2">{{ t.tagline }}</p>
            <p class="lead rise" style="--d: 3">{{ t.lead }}</p>
            <div class="cta rise" style="--d: 4">
              <!-- Icon first, so it sits on the right in RTL. -->
              <a class="lbtn primary" :href="storeUrl" target="_blank" rel="noopener noreferrer" @click="track('store_click', 'hero')">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12m0 0-5-5m5 5 5-5M5 20h14" /></svg>
                {{ t.download }}
              </a>
              <a class="lbtn ghost" :href="myketUrl" target="_blank" rel="noopener noreferrer" @click="track('store_click', 'hero_myket')">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12m0 0-5-5m5 5 5-5M5 20h14" /></svg>
                {{ t.downloadMyket }}
              </a>
            </div>
          </div>

          <div class="wrap stage">
            <div class="video-frame">
              <video
                ref="intro"
                :src="videoSrc"
                :poster="introPoster"
                :aria-label="t.videoLabel"
                width="1280"
                height="720"
                muted
                loop
                playsinline
                preload="none"
                @play="onPlay"
                @pause="playing = false"
              ></video>
              <button v-if="!started" type="button" class="video-start" @click="startVideo" :aria-label="t.play">
                <svg viewBox="0 0 24 24" width="34" height="34" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z" /></svg>
              </button>
              <div v-else class="video-ctrl">
                <button type="button" @click="togglePlay" :aria-label="playing ? t.pause : t.play">
                  <svg v-if="playing" viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1" /><rect x="14" y="5" width="4" height="14" rx="1" /></svg>
                  <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z" /></svg>
                </button>
                <button type="button" @click="toggleSound" :aria-label="muted ? t.unmute : t.mute" :aria-pressed="!muted">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 10v4h4l5 4V6L8 10z" fill="currentColor" />
                    <path v-if="muted" d="m16 9 5 6m0-6-5 6" />
                    <path v-else d="M16 9a4 4 0 0 1 0 6m2.5-8.5a7.5 7.5 0 0 1 0 11" />
                  </svg>
                  <span>{{ muted ? t.unmute : t.mute }}</span>
                </button>
              </div>
            </div>
          </div>

          <div class="ticker" aria-hidden="true">
            <div class="ticker-track">
              <span v-for="(c, i) in [...t.ticker, ...t.ticker]" :key="i">{{ c }}</span>
            </div>
          </div>
        </section>

        <!-- Verse -->
        <section class="wrap verse-row reveal">
          <p class="verse" lang="ar">أَلَا بِذِكْرِ اللَّهِ تَطْمَئِنُّ الْقُلُوبُ</p>
          <p class="verse-ref">{{ t.verseRef }}</p>
        </section>

        <!-- Features -->
        <section id="features" class="wrap features">
          <div class="section-head reveal">
            <h2>{{ t.featTitle }}</h2>
            <p>{{ t.featSub }}</p>
          </div>

          <article v-for="(f, i) in t.feats" :key="f.key" class="feature" :class="{ flip: i % 2 }">
            <div class="feature-copy reveal">
              <div class="feature-meta">
                <span class="chip" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path :d="icons[f.key]" /></svg>
                </span>
                <span class="index">{{ num(String(i + 1).padStart(2, '0')) }} <i>/</i> {{ num(String(t.feats.length).padStart(2, '0')) }}</span>
              </div>
              <h3>{{ f.title }}</h3>
              <p>{{ f.text }}</p>
              <ul>
                <li v-for="(p, j) in f.points" :key="p" :style="{ '--j': j }">
                  <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="M3.5 8.5 6.5 11.5 12.5 4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" pathLength="1" /></svg>
                  <span>{{ p }}</span>
                </li>
              </ul>
            </div>
            <div class="stage-panel reveal" :class="f.key === 'prayer' ? 'is-card' : 'is-phone'">
              <figure class="shot">
                <img :src="shots[f.key]" :alt="f.alt" loading="lazy" decoding="async" :width="sizes[f.key][0]" :height="sizes[f.key][1]" />
              </figure>
            </div>
          </article>
        </section>

        <!-- More -->
        <section class="wrap more">
          <h2 class="reveal">{{ t.moreTitle }}</h2>
          <dl class="more-list">
            <div v-for="m in t.more" :key="m[0]" class="reveal">
              <dt>{{ m[0] }}</dt>
              <dd>{{ m[1] }}</dd>
            </div>
          </dl>
        </section>

        <!-- Facts -->
        <section class="wrap facts reveal" :aria-label="t.factsLabel">
          <div v-for="s in t.stats" :key="s[1]"><strong>{{ s[0] }}</strong><span>{{ s[1] }}</span></div>
        </section>

        <!-- Privacy -->
        <section id="privacy" class="wrap privacy">
          <div class="reveal">
            <h2>{{ t.privTitle }}</h2>
            <p class="muted">{{ t.privSub }}</p>
          </div>
          <ul class="priv-list reveal">
            <li v-for="p in t.priv" :key="p">{{ p }}</li>
          </ul>
        </section>

        <!-- Support -->
        <section id="support" class="wrap support reveal">
          <div class="support-card">
            <div class="support-copy">
              <h2>{{ t.supportTitle }}</h2>
              <p>{{ t.supportText }}</p>
              <ul>
                <li v-for="p in t.supportPoints" :key="p">{{ p }}</li>
              </ul>
            </div>
            <div class="support-act">
              <a class="lbtn primary big" :href="donateUrl" target="_blank" rel="noopener noreferrer" @click="track('donate_click', 'section')">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.3C.9 8.2 3 4.5 6.7 4.5c2 0 3.5 1 5.3 3 1.8-2 3.3-3 5.3-3 3.7 0 5.8 3.7 4.3 7.2C19.5 16.4 12 21 12 21z" /></svg>
                {{ t.supportCta }}
              </a>
              <p class="support-note">{{ t.supportNote }}</p>
            </div>
          </div>
        </section>

        <!-- Download -->
        <section id="download" class="download">
          <div class="wrap download-inner reveal">
            <img src="@/assets/icons/logo.png" alt="" width="64" height="64" />
            <div>
              <h2>{{ t.finalTitle }}</h2>
              <p>{{ t.finalSub }}</p>
            </div>
            <div class="cta">
              <a class="lbtn light" :href="storeUrl" target="_blank" rel="noopener noreferrer" @click="track('store_click', 'footer')">{{ t.download }}</a>
              <a class="lbtn light" :href="myketUrl" target="_blank" rel="noopener noreferrer" @click="track('store_click', 'footer_myket')">{{ t.downloadMyket }}</a>
              <a class="lbtn outline" :href="donateUrl" target="_blank" rel="noopener noreferrer" @click="track('donate_click', 'footer')">{{ t.support }}</a>
            </div>
          </div>
        </section>
      </div>
    </main>

    <footer class="wrap foot">
      <span>{{ t.footer }}</span>
      <nav :aria-label="t.footLabel">
        <a href="/adhkar">{{ t.navAdhkar }}</a>
        <a href="/blog">{{ t.navBlog }}</a>
        <router-link to="/privacy">{{ t.navPrivacy }}</router-link>
        <a :href="donateUrl" target="_blank" rel="noopener noreferrer" @click="track('donate_click', 'foot')">{{ t.support }}</a>
        <a href="https://github.com/edrisranjbar/Nour-Adhkar-App" target="_blank" rel="noopener noreferrer" @click="track('github_click')">GitHub</a>
      </nav>
    </footer>
  </div>
</template>

<script>
import { trackEvent } from '@/services/analytics'
import introVideo from '@/assets/videos/nour-intro.mp4'
import introPoster from '@/assets/videos/nour-intro-poster.webp'
import checklist from '@/assets/images/landing/checklist.webp'
import tasbih from '@/assets/images/landing/tasbih.webp'
import prayer from '@/assets/images/landing/prayer.webp'
import adhkar from '@/assets/images/landing/adhkar.webp'

const shots = { adhkar, prayer, checklist, tasbih }
const icons = {
  adhkar: 'M12 3v2m0 14v2M5.6 5.6l1.4 1.4m10 10 1.4 1.4M3 12h2m14 0h2M5.6 18.4 7 17m10-10 1.4-1.4M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z',
  prayer: 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
  checklist: 'M9 11l2.5 2.5L16 9M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z',
  tasbih: 'M12 3.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM18 7a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM18 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM12 17.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM6 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM6 7a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z'
}
const sizes = { adhkar: [576, 1280], prayer: [532, 402], checklist: [576, 1280], tasbih: [576, 1280] }

const T = {
  fa: {
    skip: 'رفتن به محتوا', navLabel: 'منوی اصلی', footLabel: 'پیوندها', factsLabel: 'مشخصات',
    langLabel: 'زبان', themeLabel: 'تغییر حالت روشن و تاریک',
    name: 'اذکار نور', navFeatures: 'امکانات', navPrivacy: 'حریم خصوصی', navDownload: 'دریافت', navSupport: 'حمایت', navAdhkar: 'اذکار و ادعیه', navBlog: 'مقالات',
    nameA: 'اذکار', nameB: 'نور',
    videoLabel: 'ویدیوی معرفی اذکار نور', play: 'پخش ویدیو', pause: 'توقف ویدیو', unmute: 'پخش صدا', mute: 'بی‌صدا',
    tagline: 'اپلیکیشن جامع اذکار و ادعیه اهل سنت',
    lead: 'اذکار صبح و شام، ادعیهٔ قرآنی و دعاهای سنت، اوقات شرعی و ذکرشمار؛ آفلاین و بدون تبلیغ.',
    ticker: ['اذکار صبحگاه', 'اذکار شامگاه', 'ادعیهٔ قرآنی', 'دعاهای سنت', 'اوقات شرعی', 'ذکرشمار', 'قبله‌نما', 'قرآن کریم', 'چک‌لیست روزانه', 'پخش صوتی'],
    download: 'دریافت از کافه‌بازار', downloadMyket: 'دریافت از مایکت',
    verseRef: 'سورهٔ رعد، آیهٔ ۲۸',
    featTitle: 'آنچه در برنامه پیدا می‌کنید',
    featSub: 'چهار ابزار اصلی برای همراهی روزانه با ذکر و عبادت؛ ساده، آرام و بدون حواس‌پرتی.',
    feats: [
      { key: 'adhkar', title: 'اذکار صبح و شام', text: 'هر ذکر با متن عربی خوانا، ترجمهٔ فارسی و شمارندهٔ تکرار نمایش داده می‌شود.', points: ['پخش صوتی با صدای مشاری راشد العفاسی', 'افزودن به علاقه‌مندی‌ها و اشتراک‌گذاری ذکر', 'اذکار خواب، روزانه، ادعیهٔ قرآنی و دعاهای سنت'], alt: 'صفحهٔ اذکار صبحگاه با پخش صوتی، متن عربی آیةالکرسی و ترجمهٔ فارسی' },
      { key: 'prayer', title: 'اوقات شرعی', text: 'نماز بعدی و زمان باقی‌مانده تا آن، همراه با شش وقت روز، در صفحهٔ اصلی.', points: ['محاسبهٔ آفلاین بر اساس موقعیت شما', 'روش‌های محاسبهٔ شناخته‌شدهٔ بین‌المللی', 'پخش اذان با انتخاب مؤذن'], alt: 'کارت اوقات شرعی با نماز بعدی، شمارش معکوس و شش وقت روز' },
      { key: 'checklist', title: 'چک‌لیست روزانه', text: 'فرائض و اعمال مستحب روز را علامت بزنید و پیشرفت‌تان را ببینید.', points: ['نمازهای پنج‌گانه و اعمال مستحب', 'نوار پیشرفت روزانه', 'ثبت در تقویم استمرار عبادت'], alt: 'چک‌لیست روزانه با نمازها و اعمال مستحب' },
      { key: 'tasbih', title: 'ذکرشمار', text: 'ذکر را انتخاب کنید، با لمس یا با صدا بشمارید و شمارش را در تاریخچه نگه دارید.', points: ['ذکرهای آماده و ذکر دلخواه', 'شمارش صوتی', 'تاریخچهٔ ذکرهای ثبت‌شده'], alt: 'ذکرشمار با انتخاب ذکر، شمارش صوتی و تاریخچه' }
    ],
    moreTitle: 'و همچنین',
    more: [
      ['قرآن کریم', 'خواندن و جستجو در قرآن با قلم خوانای عربی.'],
      ['یادآورها', 'یادآور صبح و شام با انتخاب روزهای هفته.'],
      ['قبله‌نما', 'جهت قبله بر اساس موقعیت شما.'],
      ['مقالات', 'نوشته‌های کوتاه با امکان اشتراک‌گذاری.'],
      ['حالت روشن و تاریک', 'همراه با تنظیم اندازهٔ قلم.'],
      ['فارسی، عربی و کوردی', 'رابط کامل راست‌به‌چپ به هر سه زبان.']
    ],
    stats: [['آفلاین', 'محتوا و محاسبات'], ['بدون تبلیغ', 'و بدون ردیاب'], ['۳ زبان', 'فارسی، عربی و کوردی'], ['متن‌باز', 'رایگان و غیرانتفاعی']],
    privTitle: 'اطلاعات شما روی گوشی خودتان می‌ماند',
    privSub: 'پیشرفت، تنظیمات، ذکرهای دلخواه و تاریخچه فقط روی دستگاه ذخیره می‌شوند.',
    priv: ['بدون تبلیغات و ابزار ردیابی', 'محتوای اصلی و محاسبهٔ اوقات بدون اینترنت', 'موقعیت فقط با اجازهٔ صریح شما', 'اینترنت فقط برای دریافت صوت و بررسی نسخهٔ جدید'],
    finalTitle: 'اذکار نور را نصب کنید', finalSub: 'رایگان و سبک، برای اندروید.', support: 'حمایت از پروژه',
    supportTitle: 'اذکار نور را همراهی کنید',
    supportText: 'این برنامه بدون تبلیغ و بدون جمع‌آوری اطلاعات شخصی شما ساخته شده؛ ادامهٔ توسعه و نگهداری‌اش به حمایت کاربران بستگی دارد.',
    supportPoints: ['نگهداری و رفع اشکال برنامه', 'افزودن محتوا و بهبود طراحی', 'هزینهٔ سرور و انتشار در فروشگاه‌ها'],
    supportCta: 'حمایت مالی از اذکار نور',
    supportNote: 'هر مبلغی ارزشمند است. دعای خیر شما هم کمک بزرگی است.',
    footer: '© اذکار نور'
  },
  ar: {
    skip: 'انتقل إلى المحتوى', navLabel: 'القائمة الرئيسية', footLabel: 'روابط', factsLabel: 'مواصفات',
    langLabel: 'اللغة', themeLabel: 'تبديل الوضع الفاتح والداكن',
    name: 'أذكار نور', navFeatures: 'المزايا', navPrivacy: 'الخصوصية', navDownload: 'التحميل', navSupport: 'الدعم', navAdhkar: 'الأذكار والأدعية', navBlog: 'المقالات',
    nameA: 'أذكار', nameB: 'نور',
    videoLabel: 'فيديو تعريفي بأذكار نور', play: 'تشغيل الفيديو', pause: 'إيقاف الفيديو', unmute: 'تشغيل الصوت', mute: 'كتم الصوت',
    tagline: 'التطبيق الشامل لأذكار وأدعية أهل السنة',
    lead: 'أذكار الصباح والمساء، والأدعية القرآنية والنبوية، ومواقيت الصلاة، وعدّاد الذكر؛ دون اتصال وبلا إعلانات.',
    ticker: ['أذكار الصباح', 'أذكار المساء', 'أدعية قرآنية', 'أدعية نبوية', 'مواقيت الصلاة', 'عدّاد الذكر', 'اتجاه القبلة', 'القرآن الكريم', 'المهام اليومية', 'التشغيل الصوتي'],
    download: 'حمّل من كافه بازار', downloadMyket: 'حمّل من مايكت',
    verseRef: 'سورة الرعد، الآية ٢٨',
    featTitle: 'ما ستجده في التطبيق',
    featSub: 'أربع أدوات أساسية ترافقك يوميًا في الذكر والعبادة؛ بسيطة وهادئة وبلا تشتيت.',
    feats: [
      { key: 'adhkar', title: 'أذكار الصباح والمساء', text: 'كل ذكر بنصه العربي الواضح وعدّاد للتكرار.', points: ['تشغيل صوتي بصوت مشاري راشد العفاسي', 'الإضافة إلى المفضلة ومشاركة الذكر', 'أذكار النوم واليوم والأدعية القرآنية والنبوية'], alt: 'شاشة أذكار الصباح مع التشغيل الصوتي ونص آية الكرسي' },
      { key: 'prayer', title: 'مواقيت الصلاة', text: 'الصلاة القادمة والوقت المتبقي لها مع مواقيت اليوم الستة في الشاشة الرئيسية.', points: ['حساب دون اتصال بحسب موقعك', 'طرق حساب دولية معروفة', 'رفع الأذان مع اختيار المؤذن'], alt: 'بطاقة مواقيت الصلاة مع الصلاة القادمة والعد التنازلي' },
      { key: 'checklist', title: 'قائمة المهام اليومية', text: 'علّم على الفرائض والنوافل اليومية وتابع تقدمك.', points: ['الصلوات الخمس والأعمال المستحبة', 'شريط تقدم يومي', 'حفظ في تقويم المداومة'], alt: 'قائمة يومية بالصلوات والأعمال المستحبة' },
      { key: 'tasbih', title: 'عدّاد الذكر', text: 'اختر الذكر، وعُدّ باللمس أو بالصوت، واحفظ العدد في السجل.', points: ['أذكار جاهزة وذكر مخصص', 'العدّ الصوتي', 'سجل الأذكار المحفوظة'], alt: 'عدّاد الذكر مع اختيار الذكر والعدّ الصوتي والسجل' }
    ],
    moreTitle: 'وأيضًا',
    more: [
      ['القرآن الكريم', 'قراءة القرآن والبحث فيه بخط عربي واضح.'],
      ['التذكيرات', 'تذكير للصباح والمساء مع اختيار أيام الأسبوع.'],
      ['اتجاه القبلة', 'اتجاه القبلة بحسب موقعك.'],
      ['المقالات', 'مقالات قصيرة قابلة للمشاركة.'],
      ['الوضع الفاتح والداكن', 'مع التحكم بحجم الخط.'],
      ['الفارسية والعربية والكردية', 'واجهة كاملة من اليمين إلى اليسار بثلاث لغات.']
    ],
    stats: [['دون اتصال', 'المحتوى والحسابات'], ['بلا إعلانات', 'وبلا متتبعات'], ['٣ لغات', 'الفارسية والعربية والكردية'], ['مفتوح المصدر', 'مجاني وغير ربحي']],
    privTitle: 'بياناتك تبقى على هاتفك',
    privSub: 'التقدم والإعدادات والأذكار المخصصة والسجل محفوظة على الجهاز فقط.',
    priv: ['بلا إعلانات ولا أدوات تتبع', 'المحتوى الأساسي وحساب المواقيت دون إنترنت', 'الموقع بموافقتك الصريحة فقط', 'الإنترنت فقط لتحميل الصوت والتحقق من التحديثات'],
    finalTitle: 'ثبّت أذكار نور', finalSub: 'مجاني وخفيف، لأندرويد.', support: 'ادعم المشروع',
    supportTitle: 'كونوا معنا في أذكار نور',
    supportText: 'بُني التطبيق دون إعلانات ودون جمع بياناتك الشخصية، واستمرار تطويره وصيانته يعتمد على دعم المستخدمين.',
    supportPoints: ['صيانة التطبيق وإصلاح الأخطاء', 'إضافة المحتوى وتحسين التصميم', 'تكلفة الخادم والنشر في المتاجر'],
    supportCta: 'ادعم أذكار نور',
    supportNote: 'كل مبلغ له قيمته، ودعاؤكم عون كبير.',
    footer: '© أذكار نور'
  },
  ckb: {
    skip: 'بازدان بۆ ناوەڕۆک', navLabel: 'مێنووی سەرەکی', footLabel: 'بەستەرەکان', factsLabel: 'تایبەتمەندییەکان',
    langLabel: 'زمان', themeLabel: 'گۆڕینی دۆخی ڕووناک و تاریک',
    name: 'ئەزکاری نوور', navFeatures: 'تایبەتمەندییەکان', navPrivacy: 'نهێنیپارێزی', navDownload: 'دابەزاندن', navSupport: 'پشتگیری', navAdhkar: 'ئەزکار و دوعا', navBlog: 'وتارەکان',
    nameA: 'ئەزکاری', nameB: 'نوور',
    videoLabel: 'ڤیدیۆی ناساندنی ئەزکاری نوور', play: 'پەخشکردنی ڤیدیۆ', pause: 'ڕاگرتنی ڤیدیۆ', unmute: 'کردنەوەی دەنگ', mute: 'بێدەنگکردن',
    tagline: 'بەرنامەی گشتگیری ئەزکار و دوعاکانی ئەهلی سوننە',
    lead: 'ئەزکاری بەیانی و ئێوارە، دوعا قورئانییەکان و دوعاکانی سوننەت، کاتەکانی نوێژ و تەسبیحژمێر؛ بێ ئینتەرنێت و بێ ڕیکلام.',
    ticker: ['ئەزکاری بەیانی', 'ئەزکاری ئێوارە', 'دوعا قورئانییەکان', 'دوعاکانی سوننەت', 'کاتەکانی نوێژ', 'تەسبیحژمێر', 'قیبلەنما', 'قورئانی پیرۆز', 'لیستی ڕۆژانە', 'پەخشی دەنگی'],
    download: 'دابەزاندن لە کافە بازار', downloadMyket: 'دابەزاندن لە مایکت',
    verseRef: 'سوورەتی ڕەعد، ئایەتی ۲۸',
    featTitle: 'ئەوەی لە بەرنامەکەدا دەیدۆزیتەوە',
    featSub: 'چوار ئامرازی سەرەکی بۆ هاوڕێیەتیی ڕۆژانە لەگەڵ زیکر و پەرستش؛ سادە، ئارام و بێ سەرقاڵی.',
    feats: [
      { key: 'adhkar', title: 'ئەزکاری بەیانی و ئێوارە', text: 'هەر زیکرێک بە دەقی عەرەبیی ڕوون و ژمێرەری دووبارەبوونەوە پیشان دەدرێت.', points: ['پەخشی دەنگی بە دەنگی مشاری ڕاشد العفاسی', 'زیادکردن بۆ دڵخوازەکان و هاوبەشکردنی زیکر', 'ئەزکاری خەو و ڕۆژانە، دوعا قورئانییەکان و دوعاکانی سوننەت'], alt: 'لاپەڕەی ئەزکاری بەیانی لەگەڵ پەخشی دەنگی و دەقی ئایەتولکورسی' },
      { key: 'prayer', title: 'کاتەکانی نوێژ', text: 'نوێژی داهاتوو و کاتی ماوە تا ئەو، لەگەڵ شەش کاتەکەی ڕۆژ، لە لاپەڕەی سەرەکی.', points: ['ژمێردرانی بێ ئینتەرنێت بەپێی شوێنی تۆ', 'شێوازە ناسراوە نێودەوڵەتییەکانی ژمێردن', 'لێدانی بانگ لەگەڵ هەڵبژاردنی بانگبێژ'], alt: 'کارتی کاتەکانی نوێژ لەگەڵ نوێژی داهاتوو، ژمێرەری پاشەوە و شەش کاتەکەی ڕۆژ' },
      { key: 'checklist', title: 'لیستی ڕۆژانە', text: 'فەرز و کردارە سوننەتەکانی ڕۆژ نیشان بکە و پێشکەوتنەکەت ببینە.', points: ['پێنج نوێژەکە و کردارە سوننەتەکان', 'هێڵی پێشکەوتنی ڕۆژانە', 'تۆمارکردن لە ڕۆژژمێری بەردەوامی'], alt: 'لیستی ڕۆژانە لەگەڵ نوێژەکان و کردارە سوننەتەکان' },
      { key: 'tasbih', title: 'تەسبیحژمێر', text: 'زیکرەکە هەڵبژێرە، بە دەست یان بە دەنگ بیژمێرە و ژمارەکە لە مێژوودا بهێڵەوە.', points: ['زیکری ئامادەکراو و زیکری دڵخواز', 'ژماردنی دەنگی', 'مێژووی زیکرە تۆمارکراوەکان'], alt: 'تەسبیحژمێر لەگەڵ هەڵبژاردنی زیکر، ژماردنی دەنگی و مێژوو' }
    ],
    moreTitle: 'هەروەها',
    more: [
      ['قورئانی پیرۆز', 'خوێندنەوە و گەڕان لە قورئان بە فۆنتی عەرەبیی ڕوون.'],
      ['بیرخەرەوەکان', 'بیرخەرەوەی بەیانی و ئێوارە بە هەڵبژاردنی ڕۆژەکانی هەفتە.'],
      ['قیبلەنما', 'ئاراستەی قیبلە بەپێی شوێنی تۆ.'],
      ['وتارەکان', 'نووسراوی کورت کە دەتوانرێت هاوبەش بکرێت.'],
      ['دۆخی ڕووناک و تاریک', 'لەگەڵ ڕێکخستنی قەبارەی فۆنت.'],
      ['کوردی، فارسی و عەرەبی', 'ڕووکاری تەواو لە ڕاستەوە بۆ چەپ بە هەر سێ زمانەکە.']
    ],
    stats: [['بێ ئینتەرنێت', 'ناوەڕۆک و ژمێردنەکان'], ['بێ ڕیکلام', 'و بێ بەدواداچوون'], ['۳ زمان', 'کوردی، فارسی و عەرەبی'], ['سەرچاوە کراوە', 'خۆڕایی و ناقازانجخواز']],
    privTitle: 'زانیارییەکانت لەسەر مۆبایلەکەی خۆت دەمێننەوە',
    privSub: 'پێشکەوتن، ڕێکخستنەکان، زیکرە دڵخوازەکان و مێژوو تەنیا لەسەر ئامێرەکە پاشەکەوت دەکرێن.',
    priv: ['بێ ڕیکلام و بێ ئامرازی بەدواداچوون', 'ناوەڕۆکی سەرەکی و ژمێردنی کاتەکان بێ ئینتەرنێت', 'شوێن تەنیا بە ڕەزامەندیی ڕوونی تۆ', 'ئینتەرنێت تەنیا بۆ وەرگرتنی دەنگ و پشکنینی وەشانی نوێ'],
    finalTitle: 'ئەزکاری نوور دابەزێنە', finalSub: 'خۆڕایی و سووک، بۆ ئەندرۆید.', support: 'پشتگیریی پڕۆژە',
    supportTitle: 'هاوڕێی ئەزکاری نوور بە',
    supportText: 'ئەم بەرنامەیە بێ ڕیکلام و بێ کۆکردنەوەی زانیاریی کەسیی تۆ دروست کراوە؛ بەردەوامیی گەشەپێدان و چاودێرییەکەی بەندە بە پشتگیریی بەکارهێنەران.',
    supportPoints: ['چاودێری بەرنامە و چاککردنی هەڵەکان', 'زیادکردنی ناوەڕۆک و باشترکردنی دیزاین', 'تێچووی سێرڤەر و بڵاوکردنەوە لە مارکێتەکان'],
    supportCta: 'پشتگیریی داراییی ئەزکاری نوور',
    supportNote: 'هەر بڕێک بەنرخە. دوعای خێری تۆش یارمەتییەکی گەورەیە.',
    footer: '© ئەزکاری نوور'
  }
}

const store = {
  get(k) { try { return localStorage.getItem(k) } catch (_) { return null } },
  set(k, v) { try { localStorage.setItem(k, v) } catch (_) { /* storage unavailable: keep the in-memory value */ } }
}

// A stored value that no longer exists in T must not crash the page.
function initialLang(nav) {
  const saved = store.get('nour-lang')
  if (saved && T[saved]) return saved
  if (nav.startsWith('ar')) return 'ar'
  if (nav.startsWith('ckb') || nav.startsWith('ku')) return 'ckb'
  return 'fa'
}

export default {
  name: 'LandingView',
  data() {
    const nav = typeof navigator !== 'undefined' ? navigator.language || '' : ''
    return {
      lang: initialLang(nav),
      theme: store.get('nour-theme') || '',
      motion: true,
      shots,
      videoSrc: null,
      started: false,
      introPoster,
      playing: false,
      tracked: new Set(),
      muted: true,
      sizes,
      icons,
      storeUrl: 'https://cafebazaar.ir/app/ir.adhkar.app',
      myketUrl: 'https://myket.ir/app/ir.adhkar.app',
      donateUrl: 'https://edrisranjbar.ir/donation'
    }
  },
  computed: {
    t() { return T[this.lang] }
  },
  mounted() {
    this.motion = !window.matchMedia('(prefers-reduced-motion: reduce)').matches
    this.observe()
    this.watchSections()
  },
  beforeUnmount() {
    if (this.vio) this.vio.disconnect()
    if (this.sio) this.sio.disconnect()
    if (this.io) this.io.disconnect()
  },
  methods: {
    num(n) {
      const d = this.lang === 'ar' ? '٠١٢٣٤٥٦٧٨٩' : '۰۱۲۳۴۵۶۷۸۹'
      return String(n).replace(/\d/g, (c) => d[c])
    },
    setLang(l) {
      if (l === this.lang) return
      this.lang = l
      store.set('nour-lang', l)
      this.track('lang_switch', l)
      this.$nextTick(this.observe)
    },
    toggleTheme() {
      const dark = this.theme ? this.theme === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches
      this.theme = dark ? 'light' : 'dark'
      store.set('nour-theme', this.theme)
      this.track('theme_toggle', this.theme)
    },
    /**
     * The 3 MB intro never autoplays: it is attached and downloaded only when the visitor plays it,
     * and it pauses when scrolled out of view (without resuming on its own).
     */
    ensureVideo(then) {
      if (this.videoSrc) return then(this.$refs.intro)
      this.videoSrc = introVideo
      this.$nextTick(() => {
        const v = this.$refs.intro
        if (!v) return
        if ('IntersectionObserver' in window) {
          this.vio = new IntersectionObserver(([e]) => { if (!e.isIntersecting) v.pause() }, { threshold: 0.25 })
          this.vio.observe(v)
        }
        then(v)
      })
    },
    /** The large play button: plays muted; the sound button beside the controls turns sound on. */
    startVideo() {
      this.started = true
      this.ensureVideo((v) => {
        v.muted = true
        this.muted = true
        this.userPaused = false
        v.play().catch(() => {})
      })
    },
    track(name, label = null) {
      trackEvent(name, label)
    },
    // Some events only make sense once per page view (e.g. the looping video).
    trackOnce(name, label = null) {
      const key = label ? `${name}:${label}` : name
      if (this.tracked.has(key)) return
      this.tracked.add(key)
      trackEvent(name, label)
    },
    onPlay() {
      this.playing = true
      this.trackOnce('video_play', 'manual')
    },
    watchSections() {
      if (!('IntersectionObserver' in window)) return
      this.sio = new IntersectionObserver((entries) => entries.forEach((e) => {
        if (e.isIntersecting) { this.trackOnce('section_view', e.target.id); this.sio.unobserve(e.target) }
      }), { threshold: 0.3 })
      ;['features', 'privacy', 'download'].forEach((id) => {
        const el = this.$el.querySelector('#' + id)
        if (el) this.sio.observe(el)
      })
    },
    togglePlay() {
      this.ensureVideo((v) => {
        if (v.paused) { this.userPaused = false; v.play().catch(() => {}) } else { this.userPaused = true; v.pause() }
      })
    },
    toggleSound() {
      this.ensureVideo((v) => {
        v.muted = !v.muted
        this.muted = v.muted
        if (!v.muted) this.trackOnce('video_unmute')
        if (!v.muted && v.paused) { this.userPaused = false; v.play().catch(() => {}) }
      })
    },
    observe() {
      const els = this.$el.querySelectorAll('.reveal:not(.in)')
      if (!('IntersectionObserver' in window)) { els.forEach((el) => el.classList.add('in')); return }
      if (!this.io) {
        this.io = new IntersectionObserver((entries) => entries.forEach((e) => {
          if (e.isIntersecting) { e.target.classList.add('in'); this.io.unobserve(e.target) }
        }), { threshold: 0.15, rootMargin: '0px 0px -40px 0px' })
      }
      els.forEach((el) => this.io.observe(el))
    }
  }
}
</script>

<style scoped>
.landing {
  --bg: #f4f6f0; --surface: #fbfcf9; --line: #dfe5d8; --ink: #1b1f1a; --muted: #5a6356;
  --accent: #33602b; --accent-ink: #fff; --sand: #a58b6c; --band: #1f3a1b; --band-ink: #eef3ea;
  --shadow: 0 1px 2px rgba(27, 31, 26, 0.06), 0 12px 32px -12px rgba(27, 31, 26, 0.18);
  --ease: cubic-bezier(0.2, 0.7, 0.2, 1);
  min-height: 100vh;
  font-family: "Vazirmatn FD", Vazirmatn, Tahoma, sans-serif;
  background: var(--bg); color: var(--ink); line-height: 1.75; overflow-x: clip;
  -webkit-font-smoothing: antialiased;
  transition: background-color 0.3s, color 0.3s;
}
.landing[data-theme="dark"] {
  --bg: #121511; --surface: #191d18; --line: #2a3027; --ink: #e6ebe2; --muted: #a3ad9e;
  --accent: #a6d196; --accent-ink: #10220c; --band: #1a2417; --band-ink: #e6ebe2;
  --shadow: 0 1px 2px rgba(0, 0, 0, 0.4), 0 16px 40px -16px rgba(0, 0, 0, 0.7);
}
@media (prefers-color-scheme: dark) {
  .landing:not([data-theme="light"]) {
    --bg: #121511; --surface: #191d18; --line: #2a3027; --ink: #e6ebe2; --muted: #a3ad9e;
    --accent: #a6d196; --accent-ink: #10220c; --band: #1a2417; --band-ink: #e6ebe2;
    --shadow: 0 1px 2px rgba(0, 0, 0, 0.4), 0 16px 40px -16px rgba(0, 0, 0, 0.7);
  }
}
* { box-sizing: border-box; }
a { color: inherit; }
h1, h2, h3 { font-weight: 800; letter-spacing: -0.01em; margin: 0; }
p { margin: 0; }
.wrap { width: 100%; max-width: 1120px; margin-inline: auto; padding-inline: 20px; }
.muted { color: var(--muted); }
:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; border-radius: 6px; }
.skip { position: absolute; inset-inline-start: 12px; top: -48px; z-index: 20; background: var(--ink); color: var(--bg); padding: 8px 14px; border-radius: 8px; }
.skip:focus { top: 12px; }

/* Header */
.bar { position: sticky; top: 0; z-index: 10; background: color-mix(in srgb, var(--bg) 88%, transparent); backdrop-filter: saturate(1.4) blur(10px); border-bottom: 1px solid var(--line); }
.nav { display: flex; align-items: center; gap: 24px; height: 64px; }
.brand { display: flex; align-items: center; gap: 10px; font-weight: 800; text-decoration: none; color: var(--ink); }
.brand img { border-radius: 8px; }
.links { display: flex; gap: 24px; list-style: none; padding: 0; margin: 0; margin-inline-start: 12px; margin-inline-end: auto; }
.links a { text-decoration: none; color: var(--muted); font-size: 0.95rem; transition: color 0.2s; }
.links a:hover { color: var(--ink); }
.ctrl { display: flex; align-items: center; gap: 8px; }
.seg { display: flex; border: 1px solid var(--line); border-radius: 10px; padding: 3px; background: var(--surface); }
.seg button, .icon-btn { font: inherit; border: 0; background: none; color: var(--muted); cursor: pointer; }
.seg button { min-width: 40px; height: 34px; border-radius: 7px; font-weight: 700; transition: background-color 0.2s, color 0.2s; }
.seg button[aria-pressed="true"] { background: var(--ink); color: var(--bg); }
.icon-btn { width: 42px; height: 42px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 10px; background: var(--surface); transition: color 0.2s, transform 0.3s var(--ease); }
.icon-btn:hover { color: var(--ink); }
.icon-btn:active svg { transform: rotate(-30deg); }
.icon-btn svg { transition: transform 0.3s var(--ease); }
@media (max-width: 760px) { .links { display: none; } .ctrl { margin-inline-start: auto; } }

.nav-donate { display: inline-flex; align-items: center; height: 42px; padding: 0 16px; border-radius: 10px; background: var(--accent); color: var(--accent-ink); font-weight: 700; font-size: 0.9rem; text-decoration: none; white-space: nowrap; transition: background-color 0.2s, transform 0.2s var(--ease); }
.nav-donate:hover { background: color-mix(in srgb, var(--accent) 88%, #000); }
.nav-donate:active { transform: scale(0.97); }
@media (max-width: 420px) { .nav-donate { padding: 0 12px; font-size: 0.82rem; } .brand span { display: none; } }
section[id] { scroll-margin-top: 80px; }
html:has(.landing) { scroll-behavior: smooth; }

/* Buttons */
.cta { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
.lbtn { display: inline-flex; align-items: center; gap: 10px; min-height: 48px; padding: 0 22px; border-radius: 12px; font-weight: 700; text-decoration: none; transition: transform 0.2s var(--ease), background-color 0.2s, box-shadow 0.2s; }
.lbtn:active { transform: scale(0.97); }
.lbtn.primary { background: var(--accent); color: var(--accent-ink); box-shadow: var(--shadow); }
.lbtn.primary:hover { background: color-mix(in srgb, var(--accent) 88%, #000); }
.lbtn.primary svg { transition: transform 0.25s var(--ease); }
.lbtn.primary:hover svg { transform: translateY(2px); }
.lbtn.text { padding: 0 6px; color: var(--ink); text-decoration: underline; text-decoration-color: var(--line); text-underline-offset: 6px; text-decoration-thickness: 2px; transition: text-decoration-color 0.2s; }
.lbtn.text:hover { text-decoration-color: var(--accent); }
.lbtn.ghost { border: 1px solid var(--line); background: var(--surface); color: var(--ink); }
.lbtn.ghost:hover { border-color: color-mix(in srgb, var(--accent) 45%, var(--line)); }
.lbtn.light { background: var(--band-ink); color: var(--band); }
.lbtn.outline { border: 1px solid; border-color: color-mix(in srgb, var(--band-ink) 35%, transparent); color: var(--band-ink); }
.lbtn.outline:hover { background: color-mix(in srgb, var(--band-ink) 10%, transparent); }

/* Screenshots */
.shot { margin: 0; border-radius: 26px; overflow: hidden; background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow); }
.shot img { display: block; width: 100%; height: auto; }

/* Hero */
.hero { position: relative; isolation: isolate; overflow: hidden; padding-top: 72px; text-align: center; }
.hero-pattern { position: absolute; inset: 0; z-index: -1; color: var(--line); opacity: 0.7;
  -webkit-mask-image: radial-gradient(ellipse 60% 55% at 50% 30%, #000 20%, transparent 75%);
  mask-image: radial-gradient(ellipse 60% 55% at 50% 30%, #000 20%, transparent 75%); }
.hero-copy { display: flex; flex-direction: column; align-items: center; }
h1 { margin-top: 8px; font-size: clamp(4rem, 13vw, 9.5rem); line-height: 1.05; font-weight: 900; letter-spacing: -0.02em; display: flex; gap: 0.22em; justify-content: center; }
h1 .w2 { color: var(--accent); position: relative; }
h1 .w2::after { content: ""; position: absolute; inset-inline: 10%; bottom: -0.1em; height: 0.045em; border-radius: 1em; background: var(--sand); transform-origin: right; animation: draw 0.9s var(--ease) 0.9s both; }
.tagline { margin-top: 36px; font-size: clamp(1.25rem, 2.6vw, 1.75rem); font-weight: 700; }
.hero .lead { margin: 14px auto 32px; max-width: 30em; text-wrap: balance; font-size: 1.05rem; color: var(--muted); }
.hero .cta { justify-content: center; }

.stage { position: relative; margin-top: 56px; padding-bottom: 72px; }
.video-frame { position: relative; max-width: 1040px; margin-inline: auto; border-radius: 28px; overflow: hidden; background: #0d1a0c; aspect-ratio: 16 / 9;
  box-shadow: 0 0 0 1px var(--line), 0 2px 4px rgba(0, 0, 0, 0.06), 0 40px 80px -30px rgba(20, 40, 18, 0.45);
  animation: videoIn 1.1s var(--ease) 0.35s both; }
.video-frame video { display: block; width: 100%; height: 100%; object-fit: cover; }
.video-start { position: absolute; inset: 0; margin: auto; width: 84px; height: 84px; display: grid; place-items: center; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 50%; color: #fff; background: rgba(10, 20, 9, 0.55); backdrop-filter: blur(8px); cursor: pointer; transition: transform 0.25s var(--ease), background-color 0.2s; }
.video-start svg { margin-left: 4px; } /* optical centre of the play triangle, in any direction */
.video-start:hover { background: rgba(10, 20, 9, 0.72); transform: scale(1.06); }
.video-start:active { transform: scale(0.96); }
.video-ctrl { position: absolute; inset-inline-end: 16px; bottom: 16px; display: flex; gap: 8px; }
.video-ctrl button { font: inherit; font-size: 0.875rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; height: 42px; min-width: 42px; justify-content: center; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.22); border-radius: 999px; color: #fff; background: rgba(10, 20, 9, 0.55); backdrop-filter: blur(8px); cursor: pointer; transition: background-color 0.2s, transform 0.2s var(--ease); }
.video-ctrl button:hover { background: rgba(10, 20, 9, 0.75); }
.video-ctrl button:active { transform: scale(0.95); }
@media (max-width: 700px) {
  .stage { margin-top: 40px; padding-bottom: 48px; }
  .video-frame { border-radius: 18px; }
  .video-ctrl { inset-inline-end: 10px; bottom: 10px; }
  .video-ctrl span { display: none; }
}

.ticker { position: relative; z-index: 4; padding-block: 18px; border-block: 1px solid var(--line); background: var(--bg); overflow: hidden;
  -webkit-mask-image: linear-gradient(to left, transparent, #000 12%, #000 88%, transparent);
  mask-image: linear-gradient(to left, transparent, #000 12%, #000 88%, transparent); }
.ticker-track { display: flex; width: max-content; gap: 44px; animation: ticker 38s linear infinite; }
.ticker span { white-space: nowrap; font-weight: 700; color: var(--muted); display: inline-flex; align-items: center; gap: 44px; }
.ticker span::after { content: ""; width: 7px; height: 7px; background: var(--sand); transform: rotate(45deg); }
.ticker:hover .ticker-track { animation-play-state: paused; }

.rise { animation: rise 0.9s var(--ease) calc(var(--d) * 0.08s + 0.05s) both; }
@media (max-width: 700px) {
  .hero { padding-top: 48px; }
}

/* The verse renders only near the viewport, so the Quran face is not fetched before the first
   paint. (Applying this to every section cost more in layout work than it saved.) */
.verse-row { content-visibility: auto; contain-intrinsic-size: auto 260px; }

/* Verse */
.verse-row { text-align: center; padding-block: 64px 48px; border-bottom: 1px solid var(--line); max-width: 1080px; }
.verse { font-family: "Othman Taha", serif; font-size: clamp(1.8rem, 4vw, 2.6rem); line-height: 1.8; }
.verse-ref { color: var(--muted); font-size: 0.9rem; margin-top: 4px; }

/* Features */
.features { padding-block: 112px 48px; }
.section-head { max-width: 34em; margin-bottom: 40px; }
h2 { font-size: clamp(1.7rem, 3.2vw, 2.4rem); line-height: 1.3; }
.section-head p { color: var(--muted); margin-top: 12px; font-size: 1.05rem; text-wrap: pretty; }
.feature { display: grid; grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr); gap: clamp(32px, 6vw, 88px); align-items: center; padding-block: 56px;
  content-visibility: auto; contain-intrinsic-size: auto 560px; }
.feature.flip { grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr); }
.feature.flip .feature-copy { order: 2; }
.feature-copy { max-width: 27em; }
.feature-meta { display: flex; align-items: center; gap: 14px; margin-bottom: 22px; }
.chip { display: grid; place-items: center; width: 44px; height: 44px; border-radius: 13px; color: var(--accent);
  background: color-mix(in srgb, var(--accent) 10%, var(--surface)); border: 1px solid color-mix(in srgb, var(--accent) 22%, var(--line)); }
.chip svg { transition: transform 0.6s var(--ease); }
.feature:hover .chip svg { transform: rotate(-12deg) scale(1.06); }
.index { font-size: 0.82rem; font-weight: 700; color: var(--muted); letter-spacing: 0.08em; font-variant-numeric: tabular-nums; }
.index i { font-style: normal; color: var(--line); margin-inline: 2px; }
.feature h3 { font-size: clamp(1.5rem, 2.4vw, 1.85rem); line-height: 1.35; margin-bottom: 12px; }
.feature p { color: var(--muted); font-size: 1.02rem; text-wrap: pretty; }
.feature ul { list-style: none; padding: 0; margin: 26px 0 0; display: grid; gap: 12px; }
.feature li { display: flex; align-items: flex-start; gap: 12px; font-weight: 500; }
.feature li svg { flex: none; margin-top: 0.3em; padding: 3px; width: 22px; height: 22px; border-radius: 50%; color: var(--accent);
  background: color-mix(in srgb, var(--accent) 12%, transparent); }
.feature li svg path { stroke-dasharray: 1; stroke-dashoffset: 0; }

/* Showcase panel: the screenshot rises out of a tinted, dotted stage. */
.stage-panel { position: relative; isolation: isolate; overflow: hidden; border-radius: 32px; aspect-ratio: 5 / 4.4;
  background:
    radial-gradient(120% 90% at 50% 100%, color-mix(in srgb, var(--accent) 22%, transparent), transparent 62%),
    linear-gradient(180deg, color-mix(in srgb, var(--accent) 6%, var(--surface)), var(--surface));
  border: 1px solid var(--line); box-shadow: var(--shadow); }
.stage-panel::before { content: ""; position: absolute; inset: 0; z-index: -1; opacity: 0.6;
  background-image: radial-gradient(circle at 1px 1px, var(--line) 1px, transparent 0); background-size: 22px 22px;
  -webkit-mask-image: radial-gradient(70% 60% at 50% 30%, #000, transparent); mask-image: radial-gradient(70% 60% at 50% 30%, #000, transparent); }
.stage-panel .shot { position: absolute; margin: 0; overflow: hidden; background: var(--surface); transition: transform 0.7s var(--ease); }
.stage-panel img { display: block; width: 100%; height: auto; }
.is-phone .shot { width: 48%; inset-inline-start: 26%; top: 11%; border-radius: 28px 28px 0 0;
  border: 6px solid #1c211b; border-bottom: 0;
  box-shadow: 0 30px 60px -20px rgba(20, 40, 18, 0.45); }
.is-card .shot { width: 82%; inset-inline-start: 9%; top: 50%; translate: 0 -50%; border-radius: 22px; border: 1px solid var(--line);
  box-shadow: 0 30px 60px -24px rgba(20, 40, 18, 0.4); }
.feature:hover .is-phone .shot { transform: translateY(-10px); }
.feature:hover .is-card .shot { transform: translateY(-6px) scale(1.01); }
@media (max-width: 800px) {
  .features { padding-block: 72px 24px; }
  .feature, .feature.flip { grid-template-columns: 1fr; gap: 28px; padding-block: 36px; }
  .feature.flip .feature-copy { order: 0; }
  .stage-panel { border-radius: 24px; }
  .is-phone .shot { width: 56%; inset-inline-start: 22%; border-width: 5px; border-radius: 22px 22px 0 0; }
}

/* More */
.more { padding-block: 56px; }
.more h2 { margin-bottom: 28px; }
.more-list { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0 40px; margin: 0; }
.more-list div { padding-block: 20px; border-top: 1px solid var(--line); }
.more-list dt { font-weight: 700; margin-bottom: 4px; }
.more-list dd { margin: 0; color: var(--muted); font-size: 0.95rem; }
@media (max-width: 800px) { .more-list { grid-template-columns: 1fr 1fr; } }
@media (max-width: 520px) { .more-list { grid-template-columns: 1fr; } }

/* Facts */
.facts { display: grid; grid-template-columns: repeat(4, 1fr); border: 1px solid var(--line); border-radius: 18px; background: var(--surface); margin-block: 40px; padding-inline: 0; width: calc(100% - 40px); max-width: 1080px; }
.facts div { padding: 24px; display: grid; gap: 2px; }
.facts div + div { border-inline-start: 1px solid var(--line); }
.facts strong { font-size: 1.2rem; }
.facts span { color: var(--muted); font-size: 0.9rem; }
@media (max-width: 800px) {
  .facts { grid-template-columns: 1fr 1fr; }
  .facts div:nth-child(3) { border-inline-start: 0; }
  .facts div:nth-child(n + 3) { border-top: 1px solid var(--line); }
}

/* Privacy */
.privacy { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; padding-block: 72px 96px; align-items: start; }
.privacy h2 { margin-bottom: 14px; }
.priv-list { list-style: none; margin: 0; padding: 0; }
.priv-list li { display: flex; gap: 14px; align-items: baseline; padding-block: 14px; border-bottom: 1px solid var(--line); }
.priv-list li:first-child { border-top: 1px solid var(--line); }
.priv-list li::before { content: ""; flex: none; width: 8px; height: 8px; border-radius: 50%; background: var(--accent); transform: translateY(-2px); }
@media (max-width: 800px) { .privacy { grid-template-columns: 1fr; gap: 24px; padding-block: 48px 72px; } }

/* Support */
.support { padding-block: 0 96px; }
.support-card { display: grid; grid-template-columns: 1.4fr 1fr; gap: 40px; align-items: center; padding: 48px; border: 1px solid var(--line); border-radius: 28px; background: var(--surface); box-shadow: var(--shadow); position: relative; overflow: hidden; }
.support-card::before { content: ""; position: absolute; inset-inline-end: -80px; top: -80px; width: 260px; height: 260px; border-radius: 50%; background: color-mix(in srgb, var(--sand) 18%, transparent); }
.support-copy, .support-act { position: relative; }
.support-copy h2 { margin-bottom: 12px; }
.support-copy p { color: var(--muted); max-width: 34em; }
.support-copy ul { list-style: none; padding: 0; margin: 20px 0 0; display: grid; gap: 8px; }
.support-copy li { position: relative; padding-inline-start: 22px; }
.support-copy li::before { content: ""; position: absolute; inset-inline-start: 0; top: 0.75em; width: 10px; height: 2px; background: var(--accent); border-radius: 2px; }
.support-act { display: grid; gap: 14px; justify-items: center; text-align: center; }
.lbtn.big { min-height: 56px; padding: 0 30px; font-size: 1.05rem; width: 100%; justify-content: center; }
.support-note { color: var(--muted); font-size: 0.9rem; }
@media (max-width: 800px) { .support-card { grid-template-columns: 1fr; padding: 28px 22px; gap: 28px; } .support { padding-bottom: 64px; } }

/* Download */
.download { background: var(--band); color: var(--band-ink); }
.download-inner { display: grid; grid-template-columns: auto 1fr auto; gap: 24px; align-items: center; padding-block: 48px; }
.download-inner img { border-radius: 14px; }
.download p { opacity: 0.8; margin-top: 4px; }
@media (max-width: 800px) { .download-inner { grid-template-columns: 1fr; justify-items: start; } }

/* Footer */
.foot { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding-block: 28px; color: var(--muted); font-size: 0.9rem; }
.foot nav { display: flex; gap: 20px; }
.foot a { text-decoration: none; }
.foot a:hover { color: var(--ink); text-decoration: underline; }

/* Motion */
.reveal { opacity: 0; transform: translateY(16px); transition: opacity 0.7s var(--ease), transform 0.7s var(--ease); }
.reveal.in { opacity: 1; transform: none; }
.stage-panel.reveal { transform: translateY(32px) scale(0.98); transition-duration: 0.9s; transition-delay: 0.06s; }
.stage-panel.reveal.in { transform: none; }
.stage-panel.is-phone.reveal .shot { translate: 0 18%; transition: translate 1.1s var(--ease) 0.2s, transform 0.7s var(--ease); }
.stage-panel.is-phone.reveal.in .shot { translate: 0 0; }
.feature-copy.reveal li { opacity: 0; transform: translateX(-10px); transition: opacity 0.5s var(--ease), transform 0.5s var(--ease); transition-delay: calc(0.25s + var(--j) * 0.09s); }
.feature-copy.reveal.in li { opacity: 1; transform: none; }
.feature-copy.reveal li svg path { stroke-dashoffset: 1; transition: stroke-dashoffset 0.6s var(--ease) calc(0.4s + var(--j) * 0.09s); }
.feature-copy.reveal.in li svg path { stroke-dashoffset: 0; }
.more-list .reveal:nth-child(3n + 2) { transition-delay: 0.06s; }
.more-list .reveal:nth-child(3n + 3) { transition-delay: 0.12s; }
.swap { animation: swap 0.35s var(--ease); }
@keyframes videoIn { from { translate: 0 40px; scale: 0.97; } }
@keyframes rise { from { translate: 0 14px; } }
@keyframes draw { from { transform: scaleX(0); } }
@keyframes ticker { to { transform: translateX(50%); } }
@keyframes swap { from { opacity: 0.2; } }
@media (prefers-reduced-motion: reduce) {
  .landing *, .landing *::before, .landing *::after { animation: none !important; transition: none !important; }
  .reveal { opacity: 1; transform: none; }
}
</style>
