<template>
  <SplashScreen v-if="showSplash" :progress="progress" />
  <div v-if="!showSplash" class="home-container">
    <AppHeader title="اذکار نور" description="پلتفرم فارسی اذکار و ادعیه اسلامی" />
    
    <main class="container">
      <a
        class="bazaar-banner"
        href="https://cafebazaar.ir/app/ir.adhkar.app"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="دریافت اپلیکیشن اذکار نور از کافه بازار"
      >
        <span class="bazaar-banner__icon" aria-hidden="true">
          <img src="@/assets/icons/logo.png" alt="" />
        </span>
        <span class="bazaar-banner__content">
          <strong>اپلیکیشن اذکار نور همراه شماست</strong>
          <span>دسترسی آسان به اذکار روزانه، حتی بیرون از مرورگر</span>
        </span>
        <span class="bazaar-banner__action">
          دریافت از بازار
          <span aria-hidden="true">←</span>
        </span>
      </a>

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
.bazaar-banner {
  position: relative;
  isolation: isolate;
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 1.25rem 0;
  padding: 1rem 1.125rem;
  overflow: hidden;
  color: #fff;
  text-decoration: none;
  background: linear-gradient(125deg, #76644a 0%, #9c8466 58%, #b9a583 100%);
  border: 1px solid rgba(255, 255, 255, 0.22);
  border-radius: 18px;
  box-shadow: 0 10px 28px rgba(118, 100, 74, 0.24);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.bazaar-banner::after {
  content: '';
  position: absolute;
  z-index: -1;
  inset: -80% 58% auto -12%;
  height: 220px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
}

.bazaar-banner:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 34px rgba(118, 100, 74, 0.32);
}

.bazaar-banner:focus-visible {
  outline: 3px solid #2c2a2a;
  outline-offset: 3px;
}

.bazaar-banner__icon {
  display: grid;
  flex: 0 0 58px;
  width: 58px;
  height: 58px;
  place-items: center;
  padding: 5px;
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 5px 16px rgba(44, 42, 42, 0.2);
}

.bazaar-banner__icon img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 10px;
}

.bazaar-banner__content {
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 0.2rem;
  min-width: 0;
}

.bazaar-banner__content strong {
  font-size: 1.05rem;
  font-weight: 700;
}

.bazaar-banner__content > span {
  color: rgba(255, 255, 255, 0.82);
  font-size: 0.86rem;
}

.bazaar-banner__action {
  display: inline-flex;
  flex: 0 0 auto;
  align-items: center;
  gap: 0.45rem;
  padding: 0.7rem 0.9rem;
  color: #76644a;
  font-size: 0.9rem;
  font-weight: 700;
  white-space: nowrap;
  background: #fff;
  border-radius: 11px;
}

body.dark-mode .bazaar-banner {
  background: linear-gradient(125deg, #262626 0%, #333 60%, #49443d 100%);
  border-color: rgba(197, 177, 146, 0.24);
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.3);
}

@media (max-width: 600px) {
  .bazaar-banner {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.8rem;
    padding: 0.9rem;
    border-radius: 15px;
  }

  .bazaar-banner__icon {
    width: 52px;
    height: 52px;
    flex-basis: 52px;
  }

  .bazaar-banner__content strong {
    font-size: 0.95rem;
  }

  .bazaar-banner__content > span {
    font-size: 0.78rem;
  }

  .bazaar-banner__action {
    grid-column: 1 / -1;
    justify-content: center;
    padding: 0.65rem 0.8rem;
  }
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
</style>
