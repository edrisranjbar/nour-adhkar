<template>
  <SplashScreen v-if="showSplash" :progress="progress" />
  <div v-if="!showSplash" class="home-container">
    <AppHeader title="اذکار نور" description="پلتفرم فارسی اذکار و ادعیه اسلامی" />
    
    <main class="container">
      <SearchBar :collections="adhkarCollections" />
      <DailyVerse title="آیه روز" />
      <SpecialSection title="اذکار ویژه روزانه" />
      <h2 class="section-title">دسته بندی ها</h2>
      <CollectionsGrid :collections="adhkarCollections" />
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
import { BASE_API_URL } from '@/config';
import axios from 'axios';

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
      progress: 0,
      showSplash: false,
      adhkarCollections: [],
      dailyRemembrance: 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ، سُبْحَانَ اللَّهِ الْعَظِيمِ'
    };
  },
  async mounted() {
    this.initializeSplashScreen();
    await this.loadCollections();
    this.selectRandomDailyRemembrance();
  },
  methods: {
    async loadCollections() {
    try {
      const response = await axios.get(`${BASE_API_URL}/collections`);
      
      // Check if response.data exists and has collections array
      if (response.data?.success && Array.isArray(response.data.collections)) {
        this.adhkarCollections = response.data.collections.map(collection => ({
          name: collection.name,
          path: collection.slug,
          items: collection.adhkar_count
        }));
      } else {
        console.error('Invalid collections data format:', response.data);
        this.adhkarCollections = []; // Set empty array as fallback
      }
    } catch (error) {
      console.error('Error loading collections:', error);
      this.adhkarCollections = []; // Set empty array on error
    }
  },
    initializeSplashScreen() {
      const hasSplashBeenShown = localStorage.getItem('splashShown');

      if (!hasSplashBeenShown) {
        this.showSplash = true;
        this.disableScroll();
        this.startProgressBar();
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
      window.dispatchEvent(new Event('splash-hidden'));
    },
    disableScroll() {
      document.body.classList.add('no-scroll');
    },
    enableScroll() {
      document.body.classList.remove('no-scroll');
    },
    selectRandomDailyRemembrance() {
      if (this.adhkarCollections.length > 0) {
        // Find daily collection
        const dailyCollection = this.adhkarCollections.find(c => c.path === 'daily');
        if (dailyCollection?.items?.length > 0) {
          const randomIndex = Math.floor(Math.random() * dailyCollection.items.length);
          this.dailyRemembrance = dailyCollection.items[randomIndex].arabic;
        }
      }
    }
  }
};
</script>

<style scoped>
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
</style>