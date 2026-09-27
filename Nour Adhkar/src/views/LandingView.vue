<template>
  <main class="landing" dir="rtl">
    <section class="hero">
      <img class="logo" src="@/assets/icons/logo.png" alt="" width="96" height="96" />
      <h1>اذکار نور</h1>
      <p class="tagline">همراه روزانه برای اذکار، ادعیه، قرآن و اوقات شرعی</p>
      <a
        class="cta"
        href="https://cafebazaar.ir/app/ir.adhkar.app"
        target="_blank"
        rel="noopener noreferrer"
      >
        دریافت از کافه بازار
      </a>
    </section>

    <ul class="features">
      <li><strong>اذکار صبح و شام</strong><span>با ترجمه فارسی و شمارنده</span></li>
      <li><strong>قرآن کریم</strong><span>خواندن، جستجو و برنامه ختم</span></li>
      <li><strong>اوقات شرعی و قبله</strong><span>محاسبه آفلاین بر اساس موقعیت شما</span></li>
      <li><strong>تسبیح و یادآور</strong><span>پیگیری عادت‌ها و پیشرفت روزانه</span></li>
    </ul>

    <footer class="foot">© اذکار نور</footer>
  </main>
</template>

<script>
export default {
  name: 'LandingView'
}
</script>

<style scoped>
.landing {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 40px;
  padding: 48px 16px;
  text-align: center;
  font-family: Vazirmatn, sans-serif;
  color: #2b2620;
  background: linear-gradient(180deg, #f8f4ee 0%, #efe6d8 100%);
}
.hero { display: flex; flex-direction: column; align-items: center; gap: 12px; }
.logo { border-radius: 24px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12); }
h1 { margin: 8px 0 0; font-size: 2.25rem; font-weight: 800; }
.tagline { margin: 0; font-size: 1.1rem; color: #5e5448; max-width: 32ch; line-height: 1.8; }
.cta {
  margin-top: 12px;
  padding: 14px 28px;
  border-radius: 999px;
  background: #A79277;
  color: #fff;
  font-weight: 700;
  text-decoration: none;
  transition: background 0.2s ease;
}
.cta:hover, .cta:focus-visible { background: #8a7659; }
.features {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  width: 100%;
  max-width: 880px;
}
.features li {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 20px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.7);
}
.features span { color: #6b6054; font-size: 0.95rem; }
.foot { color: #8a7f72; font-size: 0.9rem; }

@media (prefers-color-scheme: dark) {
  .landing { color: #eee6da; background: linear-gradient(180deg, #1c1a17 0%, #12110f 100%); }
  .tagline { color: #c9bfb2; }
  .features li { background: rgba(255, 255, 255, 0.06); }
  .features span { color: #b3a898; }
}
</style>
