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
          <li><a href="#privacy">{{ t.navPrivacy }}</a></li>
          <li><a href="#download">{{ t.navDownload }}</a></li>
        </ul>
        <div class="ctrl">
          <div class="seg" role="group" :aria-label="t.langLabel">
            <button type="button" :aria-pressed="lang === 'fa'" @click="setLang('fa')" lang="fa">فا</button>
            <button type="button" :aria-pressed="lang === 'ar'" @click="setLang('ar')" lang="ar">ع</button>
          </div>
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
              <a class="lbtn primary" :href="storeUrl" target="_blank" rel="noopener noreferrer" @click="track('store_click', 'hero')">
                {{ t.download }}
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12m0 0-5-5m5 5 5-5M5 20h14" /></svg>
              </a>
              <a class="lbtn text" href="#features">{{ t.explore }}</a>
            </div>
          </div>

          <div class="wrap stage">
            <div class="video-frame">
              <video
                ref="intro"
                :src="introVideo"
                :poster="introPoster"
                :aria-label="t.videoLabel"
                width="1280"
                height="720"
                muted
                loop
                playsinline
                preload="metadata"
                @play="onPlay"
                @pause="playing = false"
              ></video>
              <div class="video-ctrl">
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
          </div>

          <article v-for="(f, i) in t.feats" :key="f.key" class="feature" :class="{ flip: i % 2 }">
            <div class="feature-copy reveal">
              <span class="index">{{ num(String(i + 1).padStart(2, '0')) }}</span>
              <h3>{{ f.title }}</h3>
              <p>{{ f.text }}</p>
              <ul>
                <li v-for="p in f.points" :key="p">{{ p }}</li>
              </ul>
            </div>
            <figure class="shot feature-shot reveal" :class="{ card: f.key === 'prayer' }">
              <img :src="shots[f.key]" :alt="f.alt" loading="lazy" decoding="async" :width="sizes[f.key][0]" :height="sizes[f.key][1]" />
            </figure>
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
              <a class="lbtn outline" href="https://edrisranjbar.ir/donation" target="_blank" rel="noopener noreferrer" @click="track('donate_click')">{{ t.support }}</a>
            </div>
          </div>
        </section>
      </div>
    </main>

    <footer class="wrap foot">
      <span>{{ t.footer }}</span>
      <nav :aria-label="t.footLabel">
        <router-link to="/privacy">{{ t.navPrivacy }}</router-link>
        <a href="https://github.com/edrisranjbar/Nour-Adhkar-App" target="_blank" rel="noopener noreferrer" @click="track('github_click')">GitHub</a>
      </nav>
    </footer>
  </div>
</template>

<script>
import { trackEvent } from '@/services/analytics'
import introVideo from '@/assets/videos/nour-intro.mp4'
import introPoster from '@/assets/videos/nour-intro-poster.jpg'
import checklist from '@/assets/images/landing/checklist.webp'
import tasbih from '@/assets/images/landing/tasbih.webp'
import prayer from '@/assets/images/landing/prayer.webp'
import adhkar from '@/assets/images/landing/adhkar.webp'

const shots = { adhkar, prayer, checklist, tasbih }
const sizes = { adhkar: [576, 1280], prayer: [532, 402], checklist: [576, 1280], tasbih: [576, 1280] }

const T = {
  fa: {
    skip: 'رفتن به محتوا', navLabel: 'منوی اصلی', footLabel: 'پیوندها', factsLabel: 'مشخصات',
    langLabel: 'زبان', themeLabel: 'تغییر حالت روشن و تاریک',
    name: 'اذکار نور', navFeatures: 'امکانات', navPrivacy: 'حریم خصوصی', navDownload: 'دریافت',
    nameA: 'اذکار', nameB: 'نور',
    videoLabel: 'ویدیوی معرفی اذکار نور', play: 'پخش ویدیو', pause: 'توقف ویدیو', unmute: 'پخش صدا', mute: 'بی‌صدا',
    tagline: 'اپلیکیشن جامع اذکار و ادعیه اهل سنت',
    lead: 'اذکار صبح و شام، ادعیهٔ قرآنی و دعاهای سنت، اوقات شرعی و ذکرشمار؛ آفلاین و بدون تبلیغ.',
    ticker: ['اذکار صبحگاه', 'اذکار شامگاه', 'ادعیهٔ قرآنی', 'دعاهای سنت', 'اوقات شرعی', 'ذکرشمار', 'قبله‌نما', 'قرآن کریم', 'چک‌لیست روزانه', 'پخش صوتی'],
    download: 'دریافت از کافه‌بازار', explore: 'دیدن امکانات',
    verseRef: 'سورهٔ رعد، آیهٔ ۲۸',
    featTitle: 'آنچه در برنامه پیدا می‌کنید',
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
      ['فارسی و عربی', 'رابط کامل راست‌به‌چپ به هر دو زبان.']
    ],
    stats: [['آفلاین', 'محتوا و محاسبات'], ['بدون تبلیغ', 'و بدون ردیاب'], ['۲ زبان', 'فارسی و عربی'], ['متن‌باز', 'رایگان و غیرانتفاعی']],
    privTitle: 'اطلاعات شما روی گوشی خودتان می‌ماند',
    privSub: 'پیشرفت، تنظیمات، ذکرهای دلخواه و تاریخچه فقط روی دستگاه ذخیره می‌شوند.',
    priv: ['بدون تبلیغات و ابزار ردیابی', 'محتوای اصلی و محاسبهٔ اوقات بدون اینترنت', 'موقعیت فقط با اجازهٔ صریح شما', 'اینترنت فقط برای دریافت صوت و بررسی نسخهٔ جدید'],
    finalTitle: 'اذکار نور را نصب کنید', finalSub: 'رایگان و سبک، برای اندروید.', support: 'حمایت از پروژه',
    footer: '© اذکار نور'
  },
  ar: {
    skip: 'انتقل إلى المحتوى', navLabel: 'القائمة الرئيسية', footLabel: 'روابط', factsLabel: 'مواصفات',
    langLabel: 'اللغة', themeLabel: 'تبديل الوضع الفاتح والداكن',
    name: 'أذكار نور', navFeatures: 'المزايا', navPrivacy: 'الخصوصية', navDownload: 'التحميل',
    nameA: 'أذكار', nameB: 'نور',
    videoLabel: 'فيديو تعريفي بأذكار نور', play: 'تشغيل الفيديو', pause: 'إيقاف الفيديو', unmute: 'تشغيل الصوت', mute: 'كتم الصوت',
    tagline: 'التطبيق الشامل لأذكار وأدعية أهل السنة',
    lead: 'أذكار الصباح والمساء، والأدعية القرآنية والنبوية، ومواقيت الصلاة، وعدّاد الذكر؛ دون اتصال وبلا إعلانات.',
    ticker: ['أذكار الصباح', 'أذكار المساء', 'أدعية قرآنية', 'أدعية نبوية', 'مواقيت الصلاة', 'عدّاد الذكر', 'اتجاه القبلة', 'القرآن الكريم', 'المهام اليومية', 'التشغيل الصوتي'],
    download: 'حمّل من كافه بازار', explore: 'استعرض المزايا',
    verseRef: 'سورة الرعد، الآية ٢٨',
    featTitle: 'ما ستجده في التطبيق',
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
      ['الفارسية والعربية', 'واجهة كاملة من اليمين إلى اليسار باللغتين.']
    ],
    stats: [['دون اتصال', 'المحتوى والحسابات'], ['بلا إعلانات', 'وبلا متتبعات'], ['لغتان', 'الفارسية والعربية'], ['مفتوح المصدر', 'مجاني وغير ربحي']],
    privTitle: 'بياناتك تبقى على هاتفك',
    privSub: 'التقدم والإعدادات والأذكار المخصصة والسجل محفوظة على الجهاز فقط.',
    priv: ['بلا إعلانات ولا أدوات تتبع', 'المحتوى الأساسي وحساب المواقيت دون إنترنت', 'الموقع بموافقتك الصريحة فقط', 'الإنترنت فقط لتحميل الصوت والتحقق من التحديثات'],
    finalTitle: 'ثبّت أذكار نور', finalSub: 'مجاني وخفيف، لأندرويد.', support: 'ادعم المشروع',
    footer: '© أذكار نور'
  }
}

const store = {
  get(k) { try { return localStorage.getItem(k) } catch (_) { return null } },
  set(k, v) { try { localStorage.setItem(k, v) } catch (_) { /* storage unavailable: keep the in-memory value */ } }
}

export default {
  name: 'LandingView',
  data() {
    const nav = typeof navigator !== 'undefined' ? navigator.language || '' : ''
    return {
      lang: store.get('nour-lang') || (nav.startsWith('ar') ? 'ar' : 'fa'),
      theme: store.get('nour-theme') || '',
      motion: true,
      shots,
      introVideo,
      introPoster,
      playing: false,
      tracked: new Set(),
      muted: true,
      sizes,
      storeUrl: 'https://cafebazaar.ir/app/ir.adhkar.app'
    }
  },
  computed: {
    t() { return T[this.lang] }
  },
  mounted() {
    this.motion = !window.matchMedia('(prefers-reduced-motion: reduce)').matches
    this.observe()
    this.setupVideo()
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
    setupVideo() {
      const v = this.$refs.intro
      if (!v || !this.motion) return
      // Autoplay only while visible, so the loop never runs off-screen.
      this.vio = new IntersectionObserver(([e]) => {
        if (e.isIntersecting && !this.userPaused) v.play().catch(() => {})
        else if (!e.isIntersecting) v.pause()
      }, { threshold: 0.25 })
      this.vio.observe(v)
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
      this.trackOnce('video_play', this.userPaused === false ? 'manual' : 'auto')
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
      const v = this.$refs.intro
      if (v.paused) { this.userPaused = false; v.play().catch(() => {}) } else { this.userPaused = true; v.pause() }
    },
    toggleSound() {
      const v = this.$refs.intro
      v.muted = !v.muted
      this.muted = v.muted
      if (!v.muted) this.trackOnce('video_unmute')
      if (!v.muted && v.paused) { this.userPaused = false; v.play().catch(() => {}) }
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
.lbtn.light { background: var(--band-ink); color: var(--band); }
.lbtn.outline { border: 1px solid; border-color: color-mix(in srgb, var(--band-ink) 35%, transparent); color: var(--band-ink); }
.lbtn.outline:hover { background: color-mix(in srgb, var(--band-ink) 10%, transparent); }

/* Screenshots */
.shot { margin: 0; border-radius: 26px; overflow: hidden; background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow); }
.shot img { display: block; width: 100%; height: auto; }

/* Hero */
@media (prefers-color-scheme: dark) {
  .landing:not([data-theme="light"])   .landing:not([data-theme="light"]) .for-light { display: none !important; }
}
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
  .phone { border-width: 4px; }
  .p-start { transform: translateX(calc(52% + var(--s, 0) * 30px)) rotate(calc(6deg + var(--s, 0) * 3deg)); }
  .p-end { transform: translateX(calc(-52% - var(--s, 0) * 30px)) rotate(calc(-6deg - var(--s, 0) * 3deg)); }
}

/* Verse */
.verse-row { text-align: center; padding-block: 64px 48px; border-bottom: 1px solid var(--line); max-width: 1080px; }
.verse { font-family: "Othman Taha", serif; font-size: clamp(1.8rem, 4vw, 2.6rem); line-height: 1.8; }
.verse-ref { color: var(--muted); font-size: 0.9rem; margin-top: 4px; }

/* Features */
.features { padding-block: 96px 40px; }
.section-head { max-width: 36em; margin-bottom: 64px; }
h2 { font-size: clamp(1.7rem, 3.2vw, 2.4rem); line-height: 1.3; }
.section-head p { color: var(--muted); margin-top: 10px; }
.feature { display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; padding-block: 48px; }
.feature + .feature { border-top: 1px solid var(--line); }
.feature.flip .feature-copy { order: 2; }
.feature-copy { max-width: 28em; }
.index { display: block; font-size: 0.85rem; font-weight: 700; color: var(--sand); letter-spacing: 0.1em; margin-bottom: 10px; }
.feature h3 { font-size: 1.6rem; margin-bottom: 12px; }
.feature p { color: var(--muted); }
.feature ul { list-style: none; padding: 0; margin: 20px 0 0; display: grid; gap: 10px; }
.feature li { position: relative; padding-inline-start: 22px; }
.feature li::before { content: ""; position: absolute; inset-inline-start: 0; top: 0.75em; width: 10px; height: 2px; background: var(--accent); border-radius: 2px; }
.feature-shot { width: 260px; justify-self: center; transition: transform 0.5s var(--ease), box-shadow 0.5s var(--ease); }
.feature-shot.card { width: min(440px, 100%); border: 0; background: none; box-shadow: none; border-radius: 26px; }
.feature-shot.card img { filter: drop-shadow(0 12px 24px rgba(27, 31, 26, 0.12)); }
.feature .feature-shot.reveal.in:hover { transform: translateY(-6px) rotate(-0.6deg); }
@media (max-width: 800px) {
  .feature { grid-template-columns: 1fr; gap: 32px; padding-block: 40px; }
  .feature.flip .feature-copy { order: 0; }
  .feature-shot { width: min(260px, 72vw); }
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
.feature .feature-shot.reveal { transform: translateY(28px) scale(0.98); transition-delay: 0.08s; }
.feature .feature-shot.reveal.in { transform: none; }
.more-list .reveal:nth-child(3n + 2) { transition-delay: 0.06s; }
.more-list .reveal:nth-child(3n + 3) { transition-delay: 0.12s; }
.swap { animation: swap 0.35s var(--ease); }
@keyframes videoIn { from { opacity: 0; translate: 0 60px; scale: 0.96; } }
@keyframes rise { from { opacity: 0; translate: 0 18px; filter: blur(6px); } }
@keyframes draw { from { transform: scaleX(0); } }
@keyframes ticker { to { transform: translateX(50%); } }
@keyframes swap { from { opacity: 0.2; } }
@media (prefers-reduced-motion: reduce) {
  .landing *, .landing *::before, .landing *::after { animation: none !important; transition: none !important; }
  .reveal { opacity: 1; transform: none; }
}
</style>
