<template>
  <footer class="app-footer">
    <div class="footer-container">
      <div class="footer-top">
        <div class="footer-section app-info">
          <h3>اذکار نور</h3>
          <p class="app-description">
            مجموعه‌ای جامع از اذکار و ادعیه اسلامی در یک اپلیکیشن ساده و کاربرپسند
          </p>
        </div>
        
        <div class="footer-section quick-links">
          <h4>لینک‌های سریع</h4>
          <ul class="footer-links-list">
            <li><RouterLink to="/collections/morning" class="footer-link">اذکار صبحگاه</RouterLink></li>
            <li><RouterLink to="/collections/night" class="footer-link">اذکار شامگاه</RouterLink></li>
            <li><RouterLink to="/counter" class="footer-link">تسبیح شمار</RouterLink></li>
            <li><RouterLink to="/blog" class="footer-link">مقالات</RouterLink></li>
          </ul>
        </div>
        
        <div class="footer-section more-links">
          <h4>لینک‌های مفید</h4>
          <ul class="footer-links-list">
            <li><RouterLink to="/about" class="footer-link">درباره ما</RouterLink></li>
            <li><RouterLink to="/donation" class="footer-link">حمایت از ما</RouterLink></li>
            <li><RouterLink to="/contribution" class="footer-link">مشارکت در پروژه</RouterLink></li>
            <li><a href="https://github.com/edrisranjbar/nour-adhkar/issues" class="footer-link">گزارش مشکل</a></li>
          </ul>
        </div>
        
        <div class="footer-section contact-info">
          <h4>ارتباط با ما</h4>
          <p class="contact-item">
            <font-awesome-icon icon="fa-solid fa-envelope" />
            <span>info@adhkar.ir</span>
          </p>
          <p class="contact-item">
            <font-awesome-icon icon="fa-solid fa-globe" />
            <span>www.adhkar.ir</span>
          </p>
          <div class="social-links">
            <a href="#" class="social-link" aria-label="تلگرام">
              <font-awesome-icon icon="fa-brands fa-telegram" />
            </a>
            <a href="#" class="social-link" aria-label="اینستاگرام">
              <font-awesome-icon icon="fa-brands fa-instagram" />
            </a>
            <a href="#" class="social-link" aria-label="گیت‌هاب">
              <font-awesome-icon icon="fa-brands fa-github" />
            </a>
          </div>
        </div>
      </div>
      
      <div class="footer-middle">
        <div class="app-features">
          <div class="feature-item">
            <div class="feature-icon">
              <font-awesome-icon icon="fa-solid fa-heart" />
            </div>
            <div class="feature-text">
              <h5>رابط کاربری زیبا</h5>
              <p>طراحی ساده و کاربرپسند</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon">
              <font-awesome-icon icon="fa-solid fa-moon" />
            </div>
            <div class="feature-text">
              <h5>حالت شب</h5>
              <p>استفاده آسان در شب</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon">
              <font-awesome-icon icon="fa-solid fa-bookmark" />
            </div>
            <div class="feature-text">
              <h5>اذکار متنوع</h5>
              <p>مجموعه کامل اذکار روزانه</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p class="copyright">
          © {{ new Date().getFullYear() }} اذکار نور - تمامی حقوق محفوظ است.
        </p>
        <p class="version">نسخه آزمایشی ۰.۱</p>
      </div>
    </div>
  
  </footer>
</template>

<script>
export default {
  name: 'AppFooter',
  methods: {
    scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
  }
}
</script>

<style scoped>
.app-footer {
  position: relative;
  background-color: #A79277;
  padding: 2rem 0;
  margin-top: 5rem;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
  border-radius: 1rem;
}

body.dark-mode .app-footer {
  background-color: #262626;
}

body.dark-mode .wave-top path {
  fill: #262626;
}

.footer-container {
  width: 100%;
  max-width: 1200px;
  padding: 0 1.5rem;
  margin: 0 auto;
  position: relative;
  z-index: 2;
  text-align: right;
}

.footer-top {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2.5rem;
  margin-bottom: 3rem;
}

.footer-section h3 {
  font-size: 1.75rem;
  color: #fff;
  margin: 0 0 1rem 0;
  font-weight: 600;
  letter-spacing: 0.5px;
  position: relative;
  display: inline-block;
}

.footer-section h3::after {
  content: '';
  position: absolute;
  bottom: -5px;
  right: 0;
  width: 60%;
  height: 3px;
  background-color: rgba(255, 255, 255, 0.5);
  border-radius: 3px;
}

.footer-section h4 {
  color: #fff;
  font-size: 1.25rem;
  margin: 0 0 1.25rem 0;
  font-weight: 500;
  position: relative;
  padding-right: 0.75rem;
}

.footer-section h4:before {
  content: '';
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 1.1rem;
  background-color: #fff;
  border-radius: 3px;
}

.app-description {
  color: rgba(255, 255, 255, 0.9);
  margin: 0 0 1.5rem 0;
  line-height: 1.7;
  max-width: 350px;
  font-size: 1rem;
  text-align: justify;
}

.newsletter {
  margin-bottom: 1.5rem;
}

.newsletter-form {
  display: flex;
  overflow: hidden;
  border-radius: 50px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.newsletter-input {
  flex: 1;
  padding: 0.75rem 1.25rem;
  border: none;
  outline: none;
  font-family: inherit;
  font-size: 0.95rem;
  color: #333;
  background-color: #fff;
}

.newsletter-button {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 45px;
  background-color: #fff;
  color: #A79277;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

body.dark-mode .newsletter-button {
  color: #262626;
}

.newsletter-button:hover {
  color: #000;
  background-color: #f5f5f5;
}

.version {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  margin: 0;
  letter-spacing: 0.3px;
}

.footer-links-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links-list li {
  margin-bottom: 0.9rem;
}

.footer-link {
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  transition: all 0.25s ease;
  font-size: 1rem;
  position: relative;
  padding-right: 1.1rem;
  display: inline-block;
}

.footer-link:before {
  content: '›';
  position: absolute;
  right: 0;
  opacity: 0.8;
  transition: transform 0.2s ease;
}

.footer-link:hover {
  color: #fff;
}

.footer-link:hover:before {
  transform: translateX(-3px);
  opacity: 1;
}

.contact-item {
  display: flex;
  align-items: center;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 1rem;
  font-size: 1rem;
}

.contact-item svg {
  margin-left: 0.75rem;
  opacity: 0.9;
  font-size: 1.1rem;
}

.social-links {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background-color: rgba(255, 255, 255, 0.15);
  color: #fff;
  border-radius: 50%;
  transition: all 0.3s ease;
  font-size: 1.2rem;
}

.social-link:hover {
  background-color: #fff;
  color: #A79277;
  transform: translateY(-3px);
  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
}

body.dark-mode .social-link:hover {
  color: #262626;
}

.footer-middle {
  margin-bottom: 2.5rem;
}

.app-features {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  justify-content: space-between;
  background-color: rgba(255, 255, 255, 0.1);
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  min-width: 250px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  padding: 0.75rem;
  border-radius: 8px;
  cursor: pointer;
}

.feature-item:hover {
  transform: translateY(-5px);
  background-color: rgba(255, 255, 255, 0.15);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.feature-item:hover .feature-icon {
  transform: scale(1.1) rotate(5deg);
  background-color: rgba(255, 255, 255, 0.3);
  box-shadow: 0 0 15px rgba(255, 255, 255, 0.4);
}

.feature-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 50px;
  height: 50px;
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  color: white;
  font-size: 1.2rem;
  transition: all 0.3s ease;
}

.feature-text h5 {
  margin: 0 0 0.3rem 0;
  color: white;
  font-size: 1rem;
  font-weight: 500;
  transition: transform 0.3s ease, color 0.3s ease;
}

.feature-text p {
  margin: 0;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.85rem;
  transition: transform 0.3s ease, color 0.3s ease;
}

.feature-item:hover .feature-text h5 {
  transform: translateX(5px);
  color: #ffffff;
}

.feature-item:hover .feature-text p {
  transform: translateX(5px);
  color: rgba(255, 255, 255, 1);
}

.footer-bottom {
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.copyright {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  margin: 0;
  letter-spacing: 0.3px;
}

.mission {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.85rem;
  margin: 0;
  font-style: italic;
}

.back-to-top {
  position: absolute;
  right: 30px;
  bottom: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 45px;
  height: 45px;
  background-color: #fff;
  color: #A79277;
  border: none;
  border-radius: 50%;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
}

body.dark-mode .back-to-top {
  color: #262626;
}

.back-to-top:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
}

/* Tablet and up */
@media (min-width: 768px) {
  .footer-top {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .footer-bottom {
    flex-direction: row;
    justify-content: space-between;
  }
}

/* Desktop and up */
@media (min-width: 1024px) {
  .footer-top {
    grid-template-columns: repeat(4, 1fr);
  }
  
  .app-features {
    justify-content: space-around;
  }
}

/* Mobile devices */
@media (max-width: 767px) {
  .app-footer {
    border-radius: 0;
    margin-top: 3rem;
  }
}
</style>