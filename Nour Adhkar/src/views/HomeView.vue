<template>
  <SplashScreen v-if="showSplash" :progress="progress" />
  <div v-if="!showSplash" class="home-container">
    <AppHeader title="اذکار نور" description="پلتفرم فارسی اذکار و ادعیه اسلامی" />
    
    <main class="container">
      <SearchBar :collections="adhkarCollections" />
      <DailyVerse title="آیه روز" />
      <SpecialSection title="اذکار ویژه روزانه" />
      <h2 class="section-title">دسته بندی ها</h2>
      <CollectionsGrid />
    </main>
    
    <AppFooter />
  </div>
</template>

<script>
import AppHeader from '@/components/Header.vue';
import AppFooter from '@/components/Footer.vue';
import SplashScreen from '@/components/SplashScreen.vue';
import SearchBar from '@/components/SearchBar.vue';
import CollectionsGrid from '@/components/CollectionsGrid.vue';
import SpecialSection from '@/components/SpecialSection.vue';
import DailyVerse from '@/components/DailyVerse.vue';
import { morningCollection } from '@/assets/js/collections/morning';
import { nightCollection } from '@/assets/js/collections/night';
import { dailyCollection } from '@/assets/js/collections/daily';
import { ramadanCollection } from '@/assets/js/collections/ramadan';
import { sleepCollection } from '@/assets/js/collections/sleep';
import { istikharaCollection } from '@/assets/js/collections/istikhara';
import { specialCollection } from '@/assets/js/collections/special';

export default {
  components: {
    SplashScreen,
    AppHeader,
    AppFooter,
    SearchBar,
    CollectionsGrid,
    SpecialSection,
    DailyVerse
  },
  data() {
    return {
      morningCollection,
      nightCollection,
      dailyCollection,
      ramadanCollection,
      sleepCollection,
      istikharaCollection,
      specialCollection,
      progress: 0,
      showSplash: false,
      adhkarCollections: [
        { name: 'صبحگاه', path: 'morning', items: morningCollection.adhkar },
        { name: 'شامگاه', path: 'night', items: nightCollection.adhkar },
        { name: 'روزانه', path: 'daily', items: dailyCollection.adhkar },
        { name: 'ماه رمضان', path: 'ramadan', items: ramadanCollection.adhkar },
        { name: 'خواب', path: 'sleep', items: sleepCollection.adhkar },
        { name: 'استخاره', path: 'istikhara', items: istikharaCollection.adhkar },
        { name: 'مناسبت‌های خاص', path: 'special', items: specialCollection.adhkar },
      ],
      dailyRemembrance: 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ، سُبْحَانَ اللَّهِ الْعَظِيمِ'
    };
  },
  mounted() {
    this.initializeSplashScreen();
    this.selectRandomDailyRemembrance();
  },
  methods: {
    initializeSplashScreen() {
      const hasSplashBeenShown = localStorage.getItem('splashShown');

      if (!hasSplashBeenShown) {
        this.showSplash = true;
        this.disableScroll();
        this.startProgressBar();
        
        // Dispatch event that splash screen is shown
        window.dispatchEvent(new Event('splash-shown'));
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
      
      // Dispatch event that splash screen is hidden
      window.dispatchEvent(new Event('splash-hidden'));
    },
    disableScroll() {
      document.body.classList.add('no-scroll');
    },
    enableScroll() {
      document.body.classList.remove('no-scroll');
    },
    navigateTo(path) {
      this.$router.push({ path: `/${path}` });
    },
    selectRandomDailyRemembrance() {
      if (dailyCollection?.adhkar?.length > 0) {
        const randomIndex = Math.floor(Math.random() * dailyCollection.adhkar.length);
        this.dailyRemembrance = dailyCollection.adhkar[randomIndex].arabic;
      }
    }
  },
};
</script>

<style scoped>
.home-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  flex: 1;
}

.section-title {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: #333;
  position: relative;
  padding-right: 1rem;
  margin-top: 0.5rem;
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

.featured-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 16px;
}

.featured-card {
  background: linear-gradient(135deg, #A79277 0%, #9C8466 100%);
  border-radius: 12px;
  padding: 1.5rem;
  color: white;
  margin-bottom: 1.5rem;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(167, 146, 119, 0.2);
  transition: transform 0.3s, box-shadow 0.3s;
  position: relative;
  overflow: hidden;
}

.featured-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(167, 146, 119, 0.3);
}

.featured-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('src/assets/images/pattern.svg') no-repeat;
  background-size: cover;
  opacity: 0.1;
  z-index: 0;
}

.featured-content {
  position: relative;
  z-index: 1;
}

.featured-content h3 {
  margin-top: 0;
  margin-bottom: 0.5rem;
  font-size: 1.25rem;
}

.featured-content p {
  font-size: 1.1rem;
  margin: 0;
  line-height: 1.8;
}
</style>