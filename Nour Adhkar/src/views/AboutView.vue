<template>
  <div class="about-container">
    <AppHeader title="درباره ما" description="آشنایی با اپلیکیشن اذکار نور" />
    
    <main class="container">
      <section class="about-section">
        <h2 class="section-title">اذکار نور چیست؟</h2>
        <p class="section-content">
          اذکار نور یک اپلیکیشن متن‌باز و رایگان است که برای دسترسی آسان به اذکار و ادعیه اسلامی طراحی شده است. هدف ما فراهم کردن ابزاری ساده، زیبا و کاربرپسند است که به کاربران اجازه می‌دهد در هر زمان و مکانی به مجموعه‌ای جامع از اذکار دسترسی داشته باشند.
        </p>
        <p class="section-content">
          این پروژه با هدف احیای فرهنگ ذکر و دعا در زندگی روزمره آغاز شده و تلاش دارد با بهره‌گیری از فناوری‌های نوین، دسترسی به منابع معنوی را برای همگان آسان‌تر سازد. اذکار نور علاوه بر ارائه متن اصلی ادعیه و اذکار به زبان عربی، ترجمه‌های دقیق فارسی و همچنین منابع و فضیلت‌های هر ذکر را نیز در اختیار کاربران قرار می‌دهد.
        </p>
        <p class="section-content">
          این اپلیکیشن با در نظر گرفتن نیازهای روزانه مسلمانان و با تمرکز بر تجربه کاربری مطلوب طراحی شده است. رابط کاربری ساده و زیبا، امکان استفاده در حالت شب، قابلیت ذخیره پیشرفت و امکان شخصی‌سازی، از جمله ویژگی‌هایی هستند که استفاده از اذکار نور را لذت‌بخش می‌سازند.
        </p>

        <h2 class="section-title mt-5">ویژگی‌های اذکار نور</h2>
        <div class="features-list">
          <div class="feature-item">
            <div class="feature-icon">
              <font-awesome-icon icon="fa-solid fa-bookmark" />
            </div>
            <div class="feature-text">
              <h3>مجموعه کامل اذکار</h3>
              <p>اذکار صبحگاهی، شامگاهی، اذکار روزانه، دعای خواب، استخاره و موارد دیگر</p>
            </div>
          </div>
          
          <div class="feature-item">
            <div class="feature-icon">
              <font-awesome-icon icon="fa-solid fa-moon" />
            </div>
            <div class="feature-text">
              <h3>حالت شب</h3>
              <p>قابلیت تغییر به حالت تاریک برای استفاده آسان در محیط‌های کم‌نور</p>
            </div>
          </div>
          
          <div class="feature-item">
            <div class="feature-icon">
              <font-awesome-icon icon="fa-solid fa-hand" />
            </div>
            <div class="feature-text">
              <h3>تسبیح شمار دیجیتال</h3>
              <p>شمارش ذکر با قابلیت تنظیم تعداد و ذخیره سازی</p>
            </div>
          </div>
          
          <div class="feature-item">
            <div class="feature-icon">
              <font-awesome-icon icon="fa-solid fa-user" />
            </div>
            <div class="feature-text">
              <h3>پروفایل کاربری</h3>
              <p>ذخیره پیشرفت، دریافت نشان‌ها و سفارشی‌سازی تجربه کاربری</p>
            </div>
          </div>
        </div>

        <h2 class="section-title mt-5">تیم اذکار نور</h2>
        <p class="section-content">
          اذکار نور توسط گروهی از توسعه‌دهندگان و طراحان مسلمان ایجاد شده است که به ارائه محتوای با کیفیت دینی در قالبی مدرن و کاربرپسند اعتقاد دارند. این پروژه به صورت متن‌باز توسعه یافته و از مشارکت افراد علاقمند استقبال می‌کند.
        </p>

        <h2 class="section-title mt-5">متن‌باز و آزاد</h2>
        <p class="section-content">
          اذکار نور یک پروژه متن‌باز است که تحت مجوز MIT منتشر شده است. شما می‌توانید کد منبع آن را در گیت‌هاب مشاهده کنید و در صورت تمایل در توسعه آن مشارکت نمایید.
        </p>
        <div class="github-link">
          <a href="https://github.com/edrisranjbar/nour-adhkar" target="_blank" class="github-button">
            <font-awesome-icon icon="fa-brands fa-github" />
            <span>مشاهده کد منبع در گیت‌هاب</span>
          </a>
        </div>

        <h2 class="section-title mt-5">حمایت از ما</h2>
        <p class="section-content">
          اذکار نور یک پروژه غیرانتفاعی است و برای ادامه توسعه و بهبود به حمایت شما نیاز دارد. شما می‌توانید با اهدای کمک مالی، گزارش مشکلات، پیشنهاد ویژگی‌های جدید یا کمک به توسعه کد، از این پروژه حمایت کنید.
        </p>
        <div class="action-buttons">
          <router-link to="/donation" class="action-button primary">
            <font-awesome-icon icon="fa-solid fa-heart" />
            <span>حمایت مالی</span>
          </router-link>
          <router-link to="/contribution" class="action-button secondary">
            <font-awesome-icon icon="fa-solid fa-code" />
            <span>مشارکت در توسعه</span>
          </router-link>
        </div>
      </section>
    </main>
    
    <AppFooter />
  </div>
</template>

<script>
import AppHeader from '@/components/Header.vue';
import AppFooter from '@/components/Footer.vue';

export default {
  name: 'AboutView',
  components: {
    AppHeader,
    AppFooter
  }
}
</script>

<style scoped>
.about-container {
  min-height: 100vh;
  max-width: 1000px;
  margin: auto;
  display: flex;
  flex-direction: column;
}

.container {
  max-width: 1000px;
  margin: 2rem auto;
  padding: 0 1.5rem;
  flex: 1;
}

.about-section {
  background: #fff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

body.dark-mode .about-section {
  background: #262626;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.section-title {
  font-size: 1.75rem;
  color: #333;
  margin-bottom: 1.5rem;
  position: relative;
  padding-right: 1rem;
}

.mt-5 {
  margin-top: 3rem;
}

.section-title::before {
  content: '';
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 32px;
  background-color: #A79277;
  border-radius: 2px;
}

body.dark-mode .section-title {
  color: #eee;
}

.section-content {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #444;
  text-align: justify;
}

body.dark-mode .section-content {
  color: #ccc;
}

.features-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.feature-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  padding: 1.25rem;
  border-radius: 12px;
}

.feature-item:hover {
  transform: translateY(-5px);
  background-color: rgba(167, 146, 119, 0.08);
  box-shadow: 0 5px 15px rgba(167, 146, 119, 0.1);
}

.feature-icon {
  width: 50px;
  height: 50px;
  min-width: 50px;
  background-color: rgba(167, 146, 119, 0.1);
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1.25rem;
  color: #A79277;
  transition: all 0.3s ease;
}

.feature-item:hover .feature-icon {
  transform: scale(1.1) rotate(5deg);
  background-color: #A79277;
  color: #fff;
}

.feature-text h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.2rem;
  color: #333;
}

body.dark-mode .feature-text h3 {
  color: #eee;
}

.feature-text p {
  margin: 0;
  font-size: 1rem;
  color: #666;
  line-height: 1.6;
}

body.dark-mode .feature-text p {
  color: #aaa;
}

.github-link {
  margin-top: 1.5rem;
  text-align: center;
}

.github-button {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  background-color: #24292e;
  color: #fff;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
}

.github-button:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.action-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-top: 1.5rem;
  justify-content: center;
}

.action-button {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.75rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
}

.action-button.primary {
  background-color: #A79277;
  color: #fff;
}

.action-button.secondary {
  background-color: rgba(167, 146, 119, 0.1);
  color: #A79277;
  border: 1px solid #A79277;
}

.action-button:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
  .features-list {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .about-section {
    padding: 1.5rem;
  }
}
</style> 