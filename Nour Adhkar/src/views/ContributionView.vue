<template>
  <div class="contribution-container">
    <AppHeader title="مشارکت در پروژه" description="کمک به بهبود و توسعه اذکار نور" />
    
    <main class="contribution-content">
      <section class="intro-section">
        <div class="intro-card">
          <div class="intro-icon">
            <font-awesome-icon icon="fa-solid fa-code" />
          </div>
          <div class="intro-text">
            <h2>مشارکت در پروژه اذکار نور</h2>
            <p>
              اذکار نور یک پروژه منبع‌باز (Open Source) است که با مشارکت کاربران و علاقه‌مندان توسعه می‌یابد. شما می‌توانید از راه‌های مختلف به بهبود این پروژه کمک کنید.
            </p>
          </div>
        </div>
      </section>

      <section class="ways-section">
        <h2 class="section-title">روش‌های مشارکت</h2>
        
        <div class="ways-grid">
          <div class="way-card">
            <div class="way-icon">
              <font-awesome-icon icon="fa-solid fa-code-branch" />
            </div>
            <div class="way-content">
              <h3>توسعه کد</h3>
              <p>اگر برنامه‌نویس هستید، می‌توانید در توسعه کد منبع اذکار نور مشارکت کنید. این پروژه از Vue.js در فرانت‌اند و Laravel در بک‌اند استفاده می‌کند.</p>
              <div class="action-links">
                <a href="https://github.com/edrisranjbar/nour-adhkar" target="_blank" class="action-link">
                  <font-awesome-icon icon="fa-brands fa-github" />
                  مخزن پروژه
                </a>
              </div>
            </div>
          </div>

          <div class="way-card">
            <div class="way-icon">
              <font-awesome-icon icon="fa-solid fa-bug" />
            </div>
            <div class="way-content">
              <h3>گزارش مشکلات</h3>
              <p>با گزارش مشکلات و باگ‌ها به ما کمک کنید تا برنامه را بهبود دهیم. جزئیات کامل مشکل و نحوه بازتولید آن را ذکر کنید.</p>
              <div class="action-links">
                <a href="https://github.com/edrisranjbar/nour-adhkar/issues" target="_blank" class="action-link">
                  <font-awesome-icon icon="fa-solid fa-exclamation-circle" />
                  گزارش مشکل جدید
                </a>
              </div>
            </div>
          </div>

          <div class="way-card">
            <div class="way-icon">
              <font-awesome-icon icon="fa-solid fa-lightbulb" />
            </div>
            <div class="way-content">
              <h3>پیشنهاد ویژگی‌های جدید</h3>
              <p>ایده‌ها و پیشنهادات خود را برای ویژگی‌های جدید با ما به اشتراک بگذارید. ما از شنیدن نظرات شما خوشحال می‌شویم!</p>
              <div class="action-links">
                <a href="mailto:edris.qeshm2+adhkar@gmail.com" class="action-link">
                  <font-awesome-icon icon="fa-solid fa-envelope" />
                  ارسال پیشنهاد
                </a>
              </div>
            </div>
          </div>

          <div class="way-card">
            <div class="way-icon">
              <font-awesome-icon icon="fa-solid fa-book" />
            </div>
            <div class="way-content">
              <h3>بهبود محتوا</h3>
              <p>در غنی‌سازی محتوای اذکار و ادعیه کمک کنید. افزودن متون جدید، تصحیح ترجمه‌ها، و بهبود منابع از جمله مشارکت‌های ارزشمند است.</p>
              <div class="action-links">
                <a href="https://github.com/edrisranjbar/nour-adhkar" target="_blank" class="action-link">
                  <font-awesome-icon icon="fa-solid fa-edit" />
                  مشارکت در محتوا
                </a>
              </div>
            </div>
          </div>

          <div class="way-card">
            <div class="way-icon">
              <font-awesome-icon icon="fa-solid fa-language" />
            </div>
            <div class="way-content">
              <h3>ترجمه برنامه</h3>
              <p>به ترجمه رابط کاربری به زبان‌های دیگر کمک کنید. افزودن زبان‌های جدید باعث دسترسی کاربران بیشتری به اذکار نور می‌شود.</p>
              <div class="action-links">
                <a href="https://github.com/edrisranjbar/nour-adhkar" target="_blank" class="action-link">
                  <font-awesome-icon icon="fa-solid fa-globe" />
                  مشارکت در ترجمه
                </a>
              </div>
            </div>
          </div>

          <div class="way-card">
            <div class="way-icon">
              <font-awesome-icon icon="fa-solid fa-heart" />
            </div>
            <div class="way-content">
              <h3>حمایت مالی</h3>
              <p>با حمایت مالی به ما کمک کنید تا هزینه‌های نگهداری سرور و توسعه برنامه را تأمین کنیم و اذکار نور را همیشه رایگان نگه داریم.</p>
              <div class="action-links">
                <RouterLink to="/donation" class="action-link highlight">
                  <font-awesome-icon icon="fa-solid fa-hand-holding-heart" />
                  حمایت مالی
                </RouterLink>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="contribution-guide">
        <h2 class="section-title">راهنمای مشارکت</h2>
        
        <div class="guide-card">
          <div class="guide-step">
            <div class="step-number">۱</div>
            <div class="step-content">
              <h3>ایجاد فورک از مخزن</h3>
              <p>ابتدا یک نسخه فورک شده از مخزن مورد نظر در حساب گیت‌هاب خود ایجاد کنید.</p>
            </div>
          </div>
          
          <div class="guide-step">
            <div class="step-number">۲</div>
            <div class="step-content">
              <h3>کلون کردن مخزن</h3>
              <p>مخزن فورک شده را در کامپیوتر خود کلون کنید:</p>
              <div class="code-block">
                <code>git clone https://github.com/YOUR-USERNAME/nour-adhkar.git</code>
              </div>
            </div>
          </div>
          
          <div class="guide-step">
            <div class="step-number">۳</div>
            <div class="step-content">
              <h3>ایجاد یک شاخه جدید</h3>
              <p>یک شاخه جدید برای تغییرات خود ایجاد کنید:</p>
              <div class="code-block">
                <code>git checkout -b feature/your-feature-name</code>
              </div>
            </div>
          </div>
          
          <div class="guide-step">
            <div class="step-number">۴</div>
            <div class="step-content">
              <h3>اعمال تغییرات</h3>
              <p>تغییرات مورد نظر خود را اعمال کنید و سپس آن‌ها را کامیت کنید:</p>
              <div class="code-block">
                <code>git add .</code>
                <code>git commit -m "توضیحات مربوط به تغییرات"</code>
              </div>
            </div>
          </div>
          
          <div class="guide-step">
            <div class="step-number">۵</div>
            <div class="step-content">
              <h3>ارسال تغییرات</h3>
              <p>تغییرات را به مخزن فورک شده خود ارسال کنید:</p>
              <div class="code-block">
                <code>git push origin feature/your-feature-name</code>
              </div>
            </div>
          </div>
          
          <div class="guide-step">
            <div class="step-number">۶</div>
            <div class="step-content">
              <h3>ایجاد درخواست ادغام (Pull Request)</h3>
              <p>در گیت‌هاب، یک درخواست ادغام از شاخه خود به شاخه اصلی پروژه ایجاد کنید و توضیحات کاملی از تغییرات را ارائه دهید.</p>
            </div>
          </div>
        </div>

        <div class="guidelines">
          <h3>نکات مهم</h3>
          <ul>
            <li>قبل از شروع کار، مطمئن شوید که تغییرات مشابهی در جریان نیست.</li>
            <li>از کدنویسی تمیز و با کیفیت اطمینان حاصل کنید.</li>
            <li>تغییرات بزرگ را به بخش‌های کوچکتر تقسیم کنید.</li>
            <li>برای هر تغییر، تست‌های مناسب اضافه کنید.</li>
            <li>مستندات لازم را به روز کنید.</li>
          </ul>
        </div>
      </section>
      
      <section class="contact-section">
        <h2 class="section-title">ارتباط با تیم توسعه</h2>
        
        <div class="contact-card">
          <p>برای ارتباط با تیم توسعه، سوالات یا پیشنهادات خود را از طریق روش‌های زیر مطرح کنید:</p>
          
          <div class="contact-options">
            <a href="mailto:edris.qeshm2+adhkar@gmail.com" class="contact-option">
              <font-awesome-icon icon="fa-solid fa-envelope" />
              <span>پست الکترونیکی</span>
            </a>
            
            <a href="https://github.com/edrisranjbar/nour-adhkar/issues" target="_blank" class="contact-option">
              <font-awesome-icon icon="fa-brands fa-github" />
              <span>گزارش مشکلات در گیت‌هاب</span>
            </a>
          </div>
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
  name: 'ContributionView',
  components: {
    AppHeader,
    AppFooter
  }
}
</script>

<style scoped>
.contribution-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.contribution-content {
  padding: 16px;
  max-width: 1000px;
  margin: 0 auto;
  flex: 1;
}

section {
  margin-bottom: 3rem;
}

.section-title {
  font-size: 1.5rem;
  color: #333;
  margin-bottom: 1.5rem;
  position: relative;
  padding-right: 1rem;
}

.section-title::before {
  content: '';
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 24px;
  background-color: #A79277;
  border-radius: 2px;
}

body.dark-mode .section-title {
  color: #eee;
}

/* Intro Section */
.intro-card {
  background-color: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-bottom: 2rem;
}

body.dark-mode .intro-card {
  background-color: #262626;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.intro-icon {
  width: 60px;
  height: 60px;
  aspect-ratio: 1 / 1;
  background-color: #A79277;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
}

.intro-text h2 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: #333;
}

body.dark-mode .intro-text h2 {
  color: #eee;
}

.intro-text p {
  font-size: 1rem;
  line-height: 1.6;
  color: #555;
  text-align: justify;
}

body.dark-mode .intro-text p {
  color: #bbb;
}

/* Ways Section */
.ways-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

.way-card {
  background-color: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: row;
  align-items: flex-start;
}

body.dark-mode .way-card {
  background-color: #262626;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.way-icon {
  width: 50px;
  height: 50px;
  min-width: 50px;
  background-color: rgba(167, 146, 119, 0.1);
  color: #A79277;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-left: 1.5rem;
}

body.dark-mode .way-icon {
  background-color: rgba(167, 146, 119, 0.2);
}

.way-content {
  flex: 1;
}

.way-content h3 {
  font-size: 1.2rem;
  margin: 0 0 0.75rem 0;
  color: #333;
}

body.dark-mode .way-content h3 {
  color: #eee;
}

.way-content p {
  font-size: 0.95rem;
  line-height: 1.6;
  color: #555;
  margin-bottom: 1.25rem;
}

body.dark-mode .way-content p {
  color: #bbb;
}

.action-links {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.action-link {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background-color: rgba(167, 146, 119, 0.1);
  color: #A79277;
  border-radius: 50px;
  text-decoration: none;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

body.dark-mode .action-link {
  background-color: rgba(167, 146, 119, 0.2);
}

.action-link svg {
  margin-left: 0.5rem;
}

.action-link:hover {
  background-color: rgba(167, 146, 119, 0.2);
  transform: translateY(-2px);
}

body.dark-mode .action-link:hover {
  background-color: rgba(167, 146, 119, 0.3);
}

.action-link.highlight {
  background-color: #A79277;
  color: #fff;
}

.action-link.highlight:hover {
  background-color: #9C8466;
}

/* Contribution Guide */
.guide-card {
  background-color: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

body.dark-mode .guide-card {
  background-color: #262626;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.guide-step {
  display: flex;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px dashed rgba(167, 146, 119, 0.3);
}

.guide-step:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.step-number {
  width: 40px;
  height: 40px;
  min-width: 40px;
  background-color: #A79277;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  font-weight: 600;
  margin-left: 1rem;
}

.step-content {
  flex: 1;
}

.step-content h3 {
  font-size: 1.1rem;
  margin: 0 0 0.5rem 0;
  color: #333;
}

body.dark-mode .step-content h3 {
  color: #eee;
}

.step-content p {
  font-size: 0.95rem;
  line-height: 1.6;
  color: #555;
  margin-bottom: 0.75rem;
}

body.dark-mode .step-content p {
  color: #bbb;
}

.code-block {
  background-color: #f5f5f5;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 0.5rem;
  direction: ltr;
  text-align: left;
  overflow-x: auto;
}

body.dark-mode .code-block {
  background-color: #1c1c1c;
}

.code-block code {
  display: block;
  font-family: monospace;
  font-size: 0.9rem;
  color: #333;
  line-height: 1.5;
}

body.dark-mode .code-block code {
  color: #eee;
}

.code-block code + code {
  margin-top: 0.5rem;
}

.guidelines {
  background-color: rgba(167, 146, 119, 0.05);
  border-radius: 16px;
  padding: 1.5rem;
}

body.dark-mode .guidelines {
  background-color: rgba(167, 146, 119, 0.1);
}

.guidelines h3 {
  font-size: 1.1rem;
  margin: 0 0 1rem 0;
  color: #333;
}

body.dark-mode .guidelines h3 {
  color: #eee;
}

.guidelines ul {
  padding-right: 1.5rem;
  margin: 0;
}

.guidelines li {
  font-size: 0.95rem;
  color: #555;
  margin-bottom: 0.75rem;
  line-height: 1.6;
}

body.dark-mode .guidelines li {
  color: #bbb;
}

/* Contact Section */
.contact-card {
  background-color: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
}

body.dark-mode .contact-card {
  background-color: #262626;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.contact-card p {
  font-size: 1rem;
  line-height: 1.6;
  color: #555;
  margin-bottom: 1.5rem;
}

body.dark-mode .contact-card p {
  color: #bbb;
}

.contact-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.contact-option {
  display: flex;
  align-items: center;
  padding: 1rem;
  background-color: rgba(167, 146, 119, 0.05);
  color: #333;
  border-radius: 12px;
  text-decoration: none;
  transition: all 0.3s ease;
}

body.dark-mode .contact-option {
  background-color: rgba(167, 146, 119, 0.1);
  color: #eee;
}

.contact-option:hover {
  background-color: rgba(167, 146, 119, 0.1);
  transform: translateY(-2px);
}

body.dark-mode .contact-option:hover {
  background-color: rgba(167, 146, 119, 0.2);
}

.contact-option svg {
  font-size: 1.2rem;
  margin-left: 1rem;
  color: #A79277;
}

@media (min-width: 768px) {
  .contribution-content {
    padding: 24px;
  }

  .intro-card {
    flex-direction: row;
    text-align: right;
    padding: 2rem;
  }

  .intro-icon {
    margin-bottom: 0;
    margin-left: 2rem;
  }

  .ways-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .contact-options {
    flex-direction: row;
  }
}

@media (min-width: 1200px) {
  .ways-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
</style> 