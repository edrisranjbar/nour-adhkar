<template>
  <section class="collections-grid">
    <section class="small-cards-row">
      <RouterLink to="/collections/morning" class="card-sm">
        <CategoryCard image-src="src/assets/images/morning.png">
          <div class="card-content">
            <div class="card-icon">
              <font-awesome-icon icon="fa-solid fa-sun" />
            </div>
            <h2 class="card-text-top">اذکار صبحگاه</h2>
            <span class="card-items-count">{{ morningCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>

      <RouterLink to="/collections/night" class="card-sm">
        <CategoryCard image-src="src/assets/images/night.png">
          <div class="card-content">
            <div class="card-icon">
              <font-awesome-icon icon="fa-solid fa-moon" />
            </div>
            <h2 class="card-text-top">اذکار شامگاه</h2>
            <span class="card-items-count">{{ nightCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>
    </section>

    <section class="small-cards-row">
      <RouterLink to="/collections/daily" class="card-sm">
        <CategoryCard image-src="src/assets/images/daily.svg">
          <div class="card-content">
            <div class="card-icon">
              <font-awesome-icon icon="fa-solid fa-calendar-day" />
            </div>
            <h2 class="card-text-top">اذکار روزانه</h2>
            <span class="card-items-count">{{ dailyCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>

      <RouterLink to="/collections/ramadan" class="card-sm">
        <CategoryCard image-src="src/assets/images/ramadan.svg">
          <div class="card-content">
            <div class="card-icon">
              <font-awesome-icon icon="fa-solid fa-star-and-crescent" />
            </div>
            <h2 class="card-text-top">اذکار ماه رمضان</h2>
            <span class="card-items-count">{{ ramadanCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>
    </section>

    <section class="small-cards-row">
      <RouterLink to="/collections/sleep" class="card-sm">
        <CategoryCard image-src="src/assets/images/sleep.jpg" size="small">
          <div class="card-content">
            <div class="card-icon">
              <font-awesome-icon icon="fa-solid fa-bed" />
            </div>
            <h2 class="card-text-top">دعای خواب</h2>
            <span class="card-items-count">{{ sleepCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>
      <RouterLink to="/collections/istikhara" class="card-sm">
        <CategoryCard image-src="src/assets/images/prayer.png" size="small">
          <div class="card-content">
            <div class="card-icon">
              <font-awesome-icon icon="fa-solid fa-hands-praying" />
            </div>
            <h2 class="card-text-top">دعای استخاره</h2>
            <span class="card-items-count">{{ istikharaCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>
    </section>
    
    <section class="small-cards-row">
      <RouterLink to="/collections/special" class="card-sm">
        <CategoryCard image-src="src/assets/images/special.svg" size="small" alt="Special occasions">
          <div class="card-content">
            <div class="card-icon">
              <font-awesome-icon icon="fa-solid fa-gift" />
            </div>
            <h2 class="card-text-top">مناسبت‌های خاص</h2>
            <span class="card-items-count">{{ specialCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>
      <RouterLink to="/counter" class="card-sm">
      <CategoryCard image-src="src/assets/images/counter.svg">
          <div class="card-content">
            <div class="card-icon">
              <font-awesome-icon icon="fa-solid fa-circle-notch" />
            </div>
            <h2 class="card-text-top">تسبیح شمار</h2>
          </div>
        </CategoryCard>
      </RouterLink>
    </section>
  </section>
</template>

<script>
import CategoryCard from "@/components/CategoryCard.vue";
import { morningCollection } from '@/assets/js/collections/morning';
import { nightCollection } from '@/assets/js/collections/night';
import { dailyCollection } from '@/assets/js/collections/daily';
import { ramadanCollection } from '@/assets/js/collections/ramadan';
import { sleepCollection } from '@/assets/js/collections/sleep';
import { istikharaCollection } from '@/assets/js/collections/istikhara';
import { specialCollection } from '@/assets/js/collections/special';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { 
  faSun, 
  faMoon, 
  faCalendarDay, 
  faStarAndCrescent, 
  faBed, 
  faHandsPraying, 
  faGift, 
  faCircleNotch 
} from '@fortawesome/free-solid-svg-icons';

library.add(
  faSun, 
  faMoon, 
  faCalendarDay, 
  faStarAndCrescent, 
  faBed, 
  faHandsPraying, 
  faGift, 
  faCircleNotch
);

export default {
  name: "CollectionsGrid",
  components: {
    CategoryCard,
    FontAwesomeIcon
  },
  data() {
    return {
      morningCollection,
      nightCollection,
      dailyCollection,
      ramadanCollection,
      sleepCollection,
      istikharaCollection,
      specialCollection
    };
  }
};
</script>

<style scoped>
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
  text-decoration: none;
  border-radius: 16px;
}

.category-card {
  height: 180px;
  border-radius: 8px;
  background-color: var(--brand-secondary);
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-light);
  background-size: cover;
  color: var(--text-light);
  position: relative;
  cursor: pointer;
  transition: all .3s ease-in-out;
  user-select: none;
}

.category-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}


.card-content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.5rem;
  height: 100%;
  padding: 1rem;
  border-radius: inherit;
}

.card-icon {
  font-size: 2rem;
  color: rgba(255, 255, 255, 0.9);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
  margin-bottom: 0.5rem;
}

.card-text-top {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 500;
  color: var(--text-light);
}

.card-items-count {
  font-size: 0.8rem;
  opacity: 0.9;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  color: var(--text-light);
}

@media (max-width: 768px) {
  .collections-grid {
    gap: 12px;
  }

  .small-cards-row {
    flex-wrap: wrap;
    gap: 12px;
  }
  
  .card-sm {
    flex-basis: calc(50% - 6px);
  }

  .card-icon {
    font-size: 1.8rem;
  }

  .card-text-top {
    font-size: 1.3rem;
  }
}

@media (max-width: 480px) {
  .card-sm {
    flex-basis: 100%;
  }

  .card-icon {
    font-size: 1.6rem;
  }

  .card-text-top {
    font-size: 1.2rem;
  }
}
</style> 