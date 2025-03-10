<template>

<SplashScreen v-if="showSplash" :progress="progress" />

  <Header title="اذکار نور" description="پلتفرم فارسی اذکار و ادعیه اسلامی" />

  <main class="container">
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

    <section class="row">
      <CategoryCard image-src="src/assets/images/sleep.jpg" size="small">
        <h2 class="card-text-top card-text-right">دعاء قبل خواب</h2>
      </CategoryCard>
      <RouterLink to="istikhara" class="card-sm">
        <CategoryCard image-src="src/assets/images/prayer.png">
          <h2 class="cart-text-bottom card-text-left">دعاء استخاره</h2>
        </CategoryCard>
      </RouterLink>
    </section>

    <RouterLink to="counter">
      <CategoryCard image-src="src/assets/images/counter.svg" style="background-position: center;" />
    </RouterLink>
  </main>

  <Footer />
</template>

<script>
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
import CategoryCard from "@/components/CategoryCard.vue";
import SplashScreen from '@/components/SplashScreen.vue';

export default {
  components: {
    SplashScreen,
    Header,
    Footer,
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
      localStorage.setItem('splashShown', 'true');
    },
  },
};
</script>