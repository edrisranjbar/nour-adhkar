<template>
  <section class="collections-grid">
    <section class="small-cards-row">
      <RouterLink to="/collections/morning" class="card-sm">
        <CategoryCard image-src="src/assets/images/morning.png">
          <div class="card-overlay">
            <h2 class="card-text-top">اذکار صبحگاه</h2>
            <span class="card-items-count">{{ morningCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>

      <RouterLink to="/collections/night" class="card-sm">
        <CategoryCard image-src="src/assets/images/night.png">
          <div class="card-overlay">
            <h2 class="card-text-top">اذکار شامگاه</h2>
            <span class="card-items-count">{{ nightCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>
    </section>

    <section class="small-cards-row">
      <RouterLink to="/collections/daily" class="card-sm">
        <CategoryCard image-src="src/assets/images/daily.svg">
          <div class="card-overlay">
            <h2 class="card-text-top">اذکار روزانه</h2>
            <span class="card-items-count">{{ dailyCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>

      <RouterLink to="/collections/ramadan" class="card-sm">
        <CategoryCard image-src="src/assets/images/ramadan.svg">
          <div class="card-overlay">
            <h2 class="card-text-top">اذکار ماه رمضان</h2>
            <span class="card-items-count">{{ ramadanCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>
    </section>

    <section class="small-cards-row">
      <RouterLink to="/collections/sleep" class="card-sm">
        <CategoryCard image-src="src/assets/images/sleep.jpg" size="small">
          <div class="card-overlay">
            <h2 class="card-text-top">دعای خواب</h2>
            <span class="card-items-count">{{ sleepCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>
      <RouterLink to="/collections/istikhara" class="card-sm">
        <CategoryCard image-src="src/assets/images/prayer.png" size="small">
          <div class="card-overlay">
            <h2 class="card-text-top">دعای استخاره</h2>
            <span class="card-items-count">{{ istikharaCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>
    </section>
    
    <section class="small-cards-row">
      <RouterLink to="/collections/special" class="card-sm">
        <CategoryCard image-src="src/assets/images/special.svg" size="small" alt="Special occasions">
          <div class="card-overlay">
            <h2 class="card-text-top">مناسبت‌های خاص</h2>
            <span class="card-items-count">{{ specialCollection?.adhkar?.length || 0 }} ذکر</span>
          </div>
        </CategoryCard>
      </RouterLink>
      <RouterLink to="/counter" class="card-sm">
      <CategoryCard image-src="src/assets/images/counter.svg">
          <div class="card-overlay">
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

export default {
  name: "CollectionsGrid",
  components: {
    CategoryCard
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

.card-text-center {
  text-align: center;
}

.card-items-count {
  font-size: 0.8rem;
  opacity: 0.9;
  margin-top: 0.25rem;
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
}

@media (max-width: 480px) {
  .card-sm {
    flex-basis: 100%;
  }
}
</style> 