<template>
  <SplashScreen v-if="showSplash" :progress="progress" />
  <div v-if="!showSplash" class="home-container">
    <AppHeader title="اذکار نور" description="پلتفرم فارسی اذکار و ادعیه اسلامی" />
    
    <!-- Search Bar -->
    <div class="search-container">
      <div class="search-bar">
        <font-awesome-icon icon="fa-solid fa-search" class="search-icon" />
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="جستجوی اذکار..." 
          class="search-input"
          @focus="isSearchFocused = true"
          @blur="handleSearchBlur"
        />
      </div>
      <div v-if="isSearchFocused && searchResults.length > 0" class="search-results">
        <div 
          v-for="result in searchResults" 
          :key="result.id" 
          class="search-result-item"
          @click="navigateToResult(result)"
        >
          <span>{{ result.title }}</span>
        </div>
      </div>
    </div>
    
    <!-- Special Morning & Night Section -->
    <section class="special-section">
      <h2 class="section-title">اذکار ویژه روزانه</h2>
      <div class="special-cards-row">
        <RouterLink to="morning" class="special-card morning-card">
          <div class="special-card-content">
            <div class="special-card-icon">
              <font-awesome-icon icon="fa-solid fa-sun" />
            </div>
            <h3>اذکار صبحگاه</h3>
            <p>هر روز صبح با این اذکار روز خود را آغاز کنید</p>
            <div class="badge">{{ morningCollection?.adhkar?.length || 0 }} ذکر</div>
          </div>
        </RouterLink>
        
        <RouterLink to="night" class="special-card night-card">
          <div class="special-card-content">
            <div class="special-card-icon">
              <font-awesome-icon icon="fa-solid fa-moon" />
            </div>
            <h3>اذکار شامگاه</h3>
            <p>پایان هر روز را با این اذکار به خوبی به پایان برسانید</p>
            <div class="badge">{{ nightCollection?.adhkar?.length || 0 }} ذکر</div>
          </div>
        </RouterLink>
      </div>
    </section>

    <main class="container">
      <h2 class="section-title">دسته بندی ها</h2>
      <section class="collections-grid">
        <section class="small-cards-row">
          <RouterLink to="morning" class="card-sm">
            <CategoryCard image-src="src/assets/images/morning.png">
              <div class="card-overlay">
                <h2 class="card-text-top">اذکار صبحگاه</h2>
                <span class="card-items-count">{{ morningCollection?.adhkar?.length || 0 }} ذکر</span>
              </div>
            </CategoryCard>
          </RouterLink>

          <RouterLink to="night" class="card-sm">
            <CategoryCard image-src="src/assets/images/night.png">
              <div class="card-overlay">
                <h2 class="card-text-left">اذکار شامگاه</h2>
                <span class="card-items-count">{{ nightCollection?.adhkar?.length || 0 }} ذکر</span>
              </div>
            </CategoryCard>
          </RouterLink>
        </section>

        <section class="small-cards-row">
          <RouterLink to="daily" class="card-sm">
            <CategoryCard image-src="src/assets/images/daily.svg">
              <div class="card-overlay">
                <h2 class="card-text-top">اذکار روزانه</h2>
                <span class="card-items-count">{{ dailyCollection?.adhkar?.length || 0 }} ذکر</span>
              </div>
            </CategoryCard>
          </RouterLink>

          <RouterLink to="ramadan" class="card-sm">
            <CategoryCard image-src="src/assets/images/ramadan.svg">
              <div class="card-overlay">
                <h2 class="card-text-left">اذکار ماه رمضان</h2>
                <span class="card-items-count">{{ ramadanCollection?.adhkar?.length || 0 }} ذکر</span>
              </div>
            </CategoryCard>
          </RouterLink>
        </section>

        <section class="small-cards-row">
          <RouterLink to="sleep" class="card-sm">
            <CategoryCard image-src="src/assets/images/sleep.jpg" size="small">
              <div class="card-overlay">
                <h2 class="card-text-top card-text-top">دعای خواب</h2>
                <span class="card-items-count">{{ sleepCollection?.adhkar?.length || 0 }} ذکر</span>
              </div>
            </CategoryCard>
          </RouterLink>
          <RouterLink to="istikhara" class="card-sm">
            <CategoryCard image-src="src/assets/images/prayer.png" size="small">
              <div class="card-overlay">
                <h2 class="card-text-bottom card-text-left">دعای استخاره</h2>
                <span class="card-items-count">{{ istikharaCollection?.adhkar?.length || 0 }} ذکر</span>
              </div>
            </CategoryCard>
          </RouterLink>
        </section>
        
        <section class="small-cards-row">
          <RouterLink to="special" class="card-sm">
            <CategoryCard image-src="src/assets/images/special.svg" size="small">
              <div class="card-overlay">
                <h2 class="card-text-top card-text-center">مناسبت‌های خاص</h2>
                <span class="card-items-count">{{ specialCollection?.adhkar?.length || 0 }} ذکر</span>
              </div>
            </CategoryCard>
          </RouterLink>
          <RouterLink to="counter" class="card-sm">
            <CategoryCard image-src="src/assets/images/counter.svg" style="background-position: center;">
              <div class="card-overlay">
                <h2 class="card-text-left">تسبیح شمار</h2>
              </div>
            </CategoryCard>
          </RouterLink>
        </section>
      </section>
    </main>
    
    <AppFooter />
  </div>
</template>

<script>
import AppHeader from '@/components/Header.vue';
import AppFooter from '@/components/Footer.vue';
import CategoryCard from "@/components/CategoryCard.vue";
import SplashScreen from '@/components/SplashScreen.vue';
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
    CategoryCard,
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
      searchQuery: '',
      isSearchFocused: false,
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
  computed: {
    searchResults() {
      if (!this.searchQuery.trim()) return [];
      
      const results = [];
      const query = this.searchQuery.trim().toLowerCase();
      
      this.adhkarCollections.forEach(collection => {
        // Add category itself if it matches
        if (collection.name.toLowerCase().includes(query)) {
          results.push({
            id: collection.path,
            title: `اذکار ${collection.name}`,
            path: collection.path,
            type: 'category'
          });
        }
        
        // Check individual adhkar
        if (collection.items) {
          collection.items.forEach(item => {
            if (
              (item.arabic && item.arabic.toLowerCase().includes(query)) || 
              (item.translation && item.translation.toLowerCase().includes(query))
            ) {
              results.push({
                id: `${collection.path}-${item.id || item.arabic.substring(0, 10)}`,
                title: item.title || `${item.arabic.substring(0, 20)}...`,
                path: collection.path,
                itemId: item.id,
                type: 'item'
              });
            }
          });
        }
      });
      
      return results.slice(0, 5); // Limit to 5 results
    }
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
    handleSearchBlur() {
      // Delay hiding to allow for clicking on results
      setTimeout(() => {
        this.isSearchFocused = false;
      }, 200);
    },
    navigateToResult(result) {
      if (result.type === 'category') {
        this.$router.push({ path: `/${result.path}` });
      } else {
        // Navigate to specific item in collection
        this.$router.push({ 
          path: `/${result.path}`,
          query: { highlight: result.itemId }
        });
      }
      this.searchQuery = '';
      this.isSearchFocused = false;
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
  padding: 16px;
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

.search-container {
  width: 100%;
  max-width: 1200px;
  margin: 1rem auto;
  padding: 0 16px;
  position: relative;
}

.search-bar {
  display: flex;
  align-items: center;
  background: white;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  position: relative;
}

body.dark-mode .search-bar {
  background: #2d2d2d;
}

.search-icon {
  color: #A79277;
  margin-left: 0.5rem;
}

.search-input {
  width: 100%;
  border: none;
  background: transparent;
  padding: 0.5rem;
  color: #333;
  direction: rtl;
  outline: none;
}

body.dark-mode .search-input {
  color: #eee;
}

.search-input::placeholder {
  color: #999;
}

.search-results {
  position: absolute;
  top: 100%;
  right: 16px;
  left: 16px;
  background: white;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 10;
  max-height: 300px;
  overflow-y: auto;
}

body.dark-mode .search-results {
  background: #2d2d2d;
}

.search-result-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid #eee;
  transition: background 0.2s;
}

body.dark-mode .search-result-item {
  border-bottom: 1px solid #444;
}

.search-result-item:hover {
  background: #f5f5f5;
}

body.dark-mode .search-result-item:hover {
  background: #3a3a3a;
}

/* Special Section for Morning and Night Adhkar */
.special-section {
  max-width: 1200px;
  padding: 0 16px;
  margin-bottom: 1.5rem;
}

.special-cards-row {
  display: flex;
  gap: 16px;
  width: 100%;
}

.special-card {
  flex: 1;
  border-radius: 16px;
  overflow: hidden;
  position: relative;
  height: 180px;
  text-decoration: none;
  display: flex;
  transition: transform 0.3s, box-shadow 0.3s;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.special-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
}

.morning-card {
  background: linear-gradient(135deg, #FFA751 0%, #FFD54F 100%);
  color: #333;
}

.night-card {
  background: linear-gradient(135deg, #141E30 0%, #243B55 100%);
  color: white;
}

.special-card-content {
  padding: 0.7rem 1.5rem;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 2;
}

.morning-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('src/assets/images/pattern.svg') no-repeat;
  background-size: cover;
  opacity: 0.1;
  z-index: 1;
}

.night-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('src/assets/images/pattern-dark.svg') no-repeat;
  background-size: cover;
  opacity: 0.1;
  z-index: 1;
}

.special-card-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.special-card h3 {
  margin: 0;
  font-size: 1.4rem;
  margin-bottom: 0.5rem;
}

.special-card p {
  margin: 0;
  font-size: 0.9rem;
  opacity: 0.9;
  margin-bottom: 0.5rem;
}

.badge {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  padding: 4px 12px;
  font-size: 0.8rem;
  align-self: flex-start;
  margin-top: auto;
  margin-bottom: 1rem;
}

.night-card .badge {
  background: rgba(255, 255, 255, 0.15);
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
  position: relative;
  overflow: hidden;
  border-radius: 12px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.card-sm:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.7) 100%);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 1rem;
  color: white;
  z-index: 1;
}

.card-text-center {
  text-align: center;
}

.card-items-count {
  font-size: 0.8rem;
  opacity: 0.9;
  margin-top: 0.25rem;
}

@media (max-width: 768px) {
  .small-cards-row, .special-cards-row {
    flex-wrap: wrap;
  }
  
  .card-sm, .special-card {
    flex-basis: calc(50% - 8px);
  }
  
  .special-card {
    height: 160px;
  }
}

@media (max-width: 480px) {
  .card-sm, .special-card {
    flex-basis: 100%;
  }
  
  .featured-content p {
    font-size: 1rem;
  }
  
  .special-card {
    height: 140px;
    margin-bottom: 16px;
  }
  
  .special-card h3 {
    font-size: 1.2rem;
  }
  
  .special-card p {
    font-size: 0.8rem;
  }
}
</style>