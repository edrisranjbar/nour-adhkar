<template>

  <SplashScreen v-if="showSplash" :progress="progress" />
  <div v-if="!showSplash">
    <AppHeader title="اذکار نور" description="پلتفرم فارسی اذکار و ادعیه اسلامی" />
    <main class="container">
      <section class="collections-grid">
        <RouterLink to="morning">
          <CategoryCard image-src="src/assets/images/morning.png">
            <h2 class="card-text-right">اذکار صبحگاه</h2>
          </CategoryCard>
        </RouterLink>

        <RouterLink to="night">
          <CategoryCard image-src="src/assets/images/night.png">
            <h2 class="card-text-left">اذکار شامگاه</h2>
          </CategoryCard>
        </RouterLink>

        <RouterLink to="daily">
          <CategoryCard image-src="src/assets/images/daily.svg">
            <h2 class="card-text-right">اذکار روزانه</h2>
          </CategoryCard>
        </RouterLink>

        <RouterLink to="ramadan">
          <CategoryCard image-src="src/assets/images/ramadan.svg">
            <h2 class="card-text-left">اذکار ماه رمضان</h2>
          </CategoryCard>
        </RouterLink>

        <section class="small-cards-row">
          <RouterLink to="sleep" class="card-sm">
            <CategoryCard image-src="src/assets/images/sleep.jpg" size="small">
              <h2 class="card-text-top card-text-right">دعای خواب</h2>
            </CategoryCard>
          </RouterLink>
          <RouterLink to="istikhara" class="card-sm">
            <CategoryCard image-src="src/assets/images/prayer.png" size="small">
              <h2 class="cart-text-bottom card-text-left">دعای استخاره</h2>
            </CategoryCard>
          </RouterLink>
          <RouterLink to="special" class="card-sm">
            <CategoryCard image-src="src/assets/images/special.svg" size="small">
              <h2 class="card-text-top card-text-center">مناسبت‌های خاص</h2>
            </CategoryCard>
          </RouterLink>
        </section>

        <RouterLink to="counter">
          <CategoryCard image-src="src/assets/images/counter.svg" style="background-position: center;" />
        </RouterLink>
      </section>
    </main>
    <div class="bottom-navigation">
      <RouterLink to="/settings" class="nav-icon">
        <font-awesome-icon icon="fa-solid fa-gear" />
        <span>تنظیمات</span>
      </RouterLink>
    </div>
    <AppFooter />
  </div>
</template>

<script>
import AppHeader from '@/components/Header.vue';
import AppFooter from '@/components/Footer.vue';
import CategoryCard from "@/components/CategoryCard.vue";
import SplashScreen from '@/components/SplashScreen.vue';

export default {
  components: {
    SplashScreen,
    AppHeader,
    AppFooter,
    CategoryCard,
  },
  data() {
    return {
      progress: 0,
      showSplash: false,
    };
  },
  mounted() {
    this.initializeSplashScreen();
  },
  methods: {
    initializeSplashScreen() {
      const hasSplashBeenShown = localStorage.getItem('splashShown');

      if (!hasSplashBeenShown) {
        this.showSplash = true;
        this.disableScroll();
        this.startProgressBar();
      }
    },

    startProgressBar() {
      const interval = setInterval(() => {
        this.progress += 3;
        if (this.progress >= 100) {
          clearInterval(interval);
          this.hideSplashScreen();
        }
      }, 100);
    },

    hideSplashScreen() {
      this.showSplash = false;
      this.enableScroll();
      localStorage.setItem('splashShown', 'true');
    },

    disableScroll() {
      document.body.classList.add('no-scroll');
    },

    enableScroll() {
      document.body.classList.remove('no-scroll');
    },
  },
};
</script>

<style scoped>
.container {
  padding: 16px;
  max-width: 1200px;
  margin: 0 auto;
}

.collections-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.small-cards-row {
  display: flex;
  gap: 16px;
  justify-content: space-between;
}

.card-sm {
  flex: 1;
  min-width: 0;
}

.card-text-center {
  text-align: center;
}

@media (max-width: 768px) {
  .small-cards-row {
    flex-wrap: wrap;
  }
  
  .card-sm {
    flex-basis: calc(50% - 8px);
  }
}

@media (max-width: 480px) {
  .card-sm {
    flex-basis: 100%;
  }
}

.bottom-navigation {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 100;
}

.nav-icon {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  background-color: #A79277;
  color: white;
  border-radius: 50%;
  text-decoration: none;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.nav-icon:hover {
  background-color: #9C8466;
  transform: scale(1.05);
}

.nav-icon span {
  font-size: 0.7rem;
  margin-top: 2px;
}

body.dark-mode .nav-icon {
  background-color: var(--dark-accent);
}

body.dark-mode .nav-icon:hover {
  background-color: #C5B192;
}
</style>