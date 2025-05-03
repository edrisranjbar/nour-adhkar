<template>
  <section class="special-section">
    <h2 class="section-title">{{ title }}</h2>
    <div class="special-cards-row">
      <RouterLink to="/collections/morning" class="special-card morning-card">
        <div class="special-card-content">
          <div class="special-card-icon">
            <font-awesome-icon icon="fa-solid fa-sun" />
          </div>
          <h3>اذکار صبحگاه</h3>
          <p>هر روز صبح با این اذکار روز خود را آغاز کنید</p>
          <div class="badge">{{ morningCollection?.adhkar?.length || 0 }} ذکر</div>
        </div>
      </RouterLink>
      
      <RouterLink to="/collections/night" class="special-card night-card">
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
</template>

<script>
import { morningCollection } from '@/assets/js/collections/morning';
import { nightCollection } from '@/assets/js/collections/night';

export default {
  name: "SpecialSection",
  props: {
    title: {
      type: String,
      default: "اذکار ویژه روزانه"
    }
  },
  data() {
    return {
      morningCollection,
      nightCollection
    };
  }
};
</script>

<style scoped>
/* Special Section for Morning and Night Adhkar */
.special-section {
  max-width: 1200px;
  margin-bottom: 1.5rem;
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
  background: url('@/assets/images/pattern.svg') no-repeat;
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
  background: url('/images/pattern-dark.svg') no-repeat;
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
  position: absolute;
  top: 1rem;
  left: 1rem;
  margin: 0;
  z-index: 3;
}

.night-card .badge {
  background: rgba(255, 255, 255, 0.15);
}

@media (max-width: 768px) {
  .special-cards-row {
    flex-wrap: wrap;
  }
  
  .special-card {
    flex-basis: calc(50% - 8px);
    height: 160px;
  }
}

@media (max-width: 480px) {
  .special-card {
    flex-basis: 100%;
    height: 140px;
    margin-bottom: 16px;
  }
  
  .special-cards-row {
    flex-direction: column;
  }
  
  .special-card h3 {
    font-size: 1.2rem;
  }
  
  .special-card p {
    font-size: 0.8rem;
  }
}
</style> 