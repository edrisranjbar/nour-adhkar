<template>
  <div class="counter-view">
    <header>
      <div class="d-flex">
        <RouterLink to="/" class="d-flex align-items-center">
          <img class="appbar-action-button" src="@/assets/icons/back-button.svg" alt="برگشتن">
        </RouterLink>
        <h1 id="modal-title">شمارنده اذکار</h1>
      </div>
    </header>

    <main class="counter-container">

      <!-- Adhkars Selection Grid -->
      <section class="adhkars-section">
        <div class="section-header">
          <h2>اذکار</h2>
        </div>
        <div v-if="adhkars.length > 0" class="adhkars-grid">
          <button 
            v-for="(dhikr, idx) in adhkars.slice(0, 4)" 
            :key="dhikr.id || idx" 
            class="dhikr-chip" 
            :class="{ active: currentDhikr.id === dhikr.id }"
            @click="selectDhikr(dhikr)"
          >
            <span class="chip-title">{{ dhikr.title }}</span>
          </button>
        </div>
        <div v-else class="empty-state">
          <div class="empty-icon">
            <font-awesome-icon icon="fa-solid fa-book-open" size="3x" />
          </div>
          <h3>هیچ ذکری یافت نشد</h3>
          <p>برای شروع، از دکمه «افزودن ذکر» استفاده کنید.</p>
        </div>
      </section>

      <div class="counter-display" @click="handleCounterClick">
        <h2 v-if="currentDhikr.title">{{ currentDhikr.title }}</h2>
        <p v-if="currentDhikr.translation" class="translation">{{ currentDhikr.translation }}</p>
        <div class="counter-number">{{ currentCount }}</div>
        <div class="counter-text">مرتبه</div>
        <div v-if="currentDhikr.count" class="progress-bar">
          <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
        </div>

        <div class="counter-controls" @click.stop>
          <button class="counter-button count-button" @click="incrementCount">
            شمارش
          </button>
          <button class="counter-button reset-button" @click="resetCounter">
            بازنشانی
          </button>
        </div>
      </div>

    </main>

    <!-- Toast Notifications -->
    <div class="toast-container">
      <transition-group name="toast">
        <div v-for="toast in toasts" :key="toast.id" class="toast" :class="toast.type">
          <div class="toast-content">
            <span class="toast-message">{{ toast.message }}</span>
          </div>
        </div>
      </transition-group>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import axios from 'axios';
import { BASE_API_URL } from '@/config';

export default {
  name: 'CounterView',
  data() {
    return {
      adhkars: [],
      currentDhikr: {},
      currentCount: 0,
      hasCompleted: false,
      toasts: []
    }
  },
  computed: {
    ...mapState(['user']),
    ...mapGetters(['isAuthenticated']),
    progressPercentage() {
      if (!this.currentDhikr.count) return 0;
      return (this.currentCount / this.currentDhikr.count) * 100;
    },
  },
  methods: {
    async fetchAdhkars() {
      try {
        // First get the daily collection
        const collectionsResponse = await axios.get(`${BASE_API_URL}/collections/daily`);
        const dailyCollection = collectionsResponse.data.collection;
        
        if (!dailyCollection) {
          console.error('Daily collection not found');
          this.addToast('مجموعه اذکار روزانه یافت نشد', 'error');
          return;
        }
        
        this.adhkars = dailyCollection.adhkar || [];
        
        // Select the first dhikr by default if available
        if (this.adhkars.length > 0 && !this.currentDhikr.id) {
          this.selectDhikr(this.adhkars[0]);
        }
      } catch (error) {
        console.error('Error fetching adhkars:', error);
        this.addToast('خطا در دریافت اذکار', 'error');
      }
    },
    selectDhikr(dhikr) {
      this.currentDhikr = dhikr;
      this.currentCount = 0;
      this.hasCompleted = false;
    },
    handleCounterClick() {
      // Only increment if we have a current dhikr selected
      if (!this.currentDhikr.id) return;
      // If already completed, tapping goes to next dhikr (if exists)
      if (this.currentDhikr.count && this.currentCount >= this.currentDhikr.count) {
        const currentIndex = this.adhkars.findIndex(d => d.id === this.currentDhikr.id);
        const next = this.adhkars[currentIndex + 1];
        if (next) {
          this.selectDhikr(next);
          return;
        }
      }
      this.incrementCount();
    },
    incrementCount() {
      if (!this.currentDhikr.id) {
        this.addToast('لطفاً ابتدا یک ذکر انتخاب کنید', 'warning');
        return;
      }

      if (this.currentCount < this.currentDhikr.count) {
        this.currentCount++;

        // Check if we've reached the target count
        if (this.currentCount === this.currentDhikr.count) {
          this.completeCounter();
        }
      }
    },
    resetCounter() {
      this.currentCount = 0;
      this.hasCompleted = false;
    },
    completeCounter() {
      this.hasCompleted = true;
      this.addToast('تبریک! شما ذکر را به پایان رساندید', 'success');
      // Optional: Auto-advance immediately if user taps after completion is not required
    },
    addToast(message, type) {
      const toast = {
        id: Date.now(),
        message,
        type
      };
      this.toasts.push(toast);
      setTimeout(() => {
        this.toasts = this.toasts.filter(t => t.id !== toast.id);
      }, 3000);
    }
  },
  mounted() {
    this.fetchAdhkars();
  },
  beforeUnmount() {
  }
}
</script>

<style scoped>
.counter-view {
  min-height: 100vh;
  max-width: 1000px;
  margin: auto;
  background: #D1BB9E;
  padding-top: 20px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.add-dhikr-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #9C8466;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.add-dhikr-button i {
  font-size: 16px;
}

.add-dhikr-button:hover {
  background: #A79277;
  transform: translateY(-2px);
  box-shadow: rgba(149, 157, 165, 0.5) 0 8px 24px;
}

header {
  height: 80px;
  padding: 0 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #A79277;
  background-image: url('@/assets/images/pattern.svg');
  background-repeat: repeat;
  background-size: cover;
  color: #ffffff;
  margin: 0;
}

body.dark-mode header {
  background: #1E1E1E;
}

.counter-container {
  padding: 20px 0;
  margin: 0 auto;
}

.counter-display {
  text-align: center;
  margin: 40px 0;
  padding: 20px;
  background: rgba(240, 240, 240, .67);
  border-radius: 8px;
  box-shadow: rgba(0, 0, 0, .25) 0 4px 4px;
  cursor: pointer;
}

.counter-number {
  margin-top: 16px;
  font-size: 72px;
  font-weight: 700;
  color: #9C8466;
  line-height: 1;
  user-select: none;
}

.counter-text {
  font-size: 24px;
  color: #2C2A2A;
  margin-top: 8px;
  font-weight: 300;
  user-select: none;
}

.counter-controls {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 32px;
  margin-bottom: 32px;
  user-select: none;
}

.counter-button {
  padding: 16px 32px;
  border: none;
  border-radius: 8px;
  font-size: 18px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.count-button {
  background: #9C8466;
  color: #ffffff;
  min-width: 120px;
}

.count-button:hover {
  transform: translateY(-2px);
  box-shadow: rgba(149, 157, 165, 0.5) 0 8px 24px;
}

.count-button:active {
  transform: translateY(0);
}

.progress-bar {
  width: 100%;
  height: 16px;
  background: rgba(156, 132, 102, 0.2);
  border-radius: 2px;
  margin-top: 16px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #9C8466;
  transition: width 0.3s ease;
}

.start-button {
  background: #9C8466;
  color: #ffffff;
  min-width: 120px;
}

.start-button:hover {
  transform: translateY(-2px);
  box-shadow: rgba(149, 157, 165, 0.5) 0 8px 24px;
}

.start-button.active {
  background: #A79277;
}

.reset-button {
  background: rgba(240, 240, 240, .67);
  color: #2C2A2A;
  border: 2px solid #9C8466;
}

.reset-button:hover {
  background: #9C8466;
  color: #ffffff;
}

.dhikr-info {
  text-align: center;
  margin-bottom: 40px;
  padding: 20px;
  background: rgba(240, 240, 240, .67);
  border-radius: 8px;
  box-shadow: rgba(0, 0, 0, .25) 0 4px 4px;
}

.dhikr-info h2 {
  font-size: 24px;
  color: #9C8466;
  margin-bottom: 8px;
  font-weight: 500;
}

.dhikr-info .translation {
  color: #2C2A2A;
  font-size: 16px;
  font-weight: 300;
}

section {
  margin-bottom: 40px;
}

h2 {
  color: #9C8466;
  margin-bottom: 20px;
  font-size: 24px;
  font-weight: 500;
}

.carousel-container {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 20px 0;
}

.carousel-content {
  width: 100%;
  margin: 0 15px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.carousel-slide {
  display: flex;
  justify-content: center;
  gap: 15px;
  width: 100%;
}

.carousel-button {
  background: #9C8466;
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.carousel-button:hover:not(:disabled) {
  background: #A79277;
  transform: translateY(-2px);
  box-shadow: rgba(149, 157, 165, 0.5) 0 8px 24px;
}

.carousel-button:disabled {
  background: #cccccc;
  cursor: not-allowed;
  opacity: 0.5;
}

.carousel-indicators {
  display: flex;
  justify-content: center;
  margin-top: 15px;
}

.indicator-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: #e6dfd5;
  margin: 0 5px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.indicator-dot.active {
  background-color: #9C8466;
  transform: scale(1.2);
}

.dhikr-card {
  background: rgba(240, 240, 240, .67);
  border-radius: 8px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: rgba(0, 0, 0, .25) 0 4px 4px;
  flex: 1;
  min-width: 0;
  min-height: 150px;
  display: flex;
  flex-direction: column;
}

.dhikr-card:hover {
  transform: translateY(-2px);
  box-shadow: rgba(149, 157, 165, 0.5) 0 8px 24px;
}

.dhikr-card.active {
  border: 2px solid #9C8466;
  background: rgba(156, 132, 102, 0.1);
}

.dhikr-card h3 {
  color: #2C2A2A;
  margin: 0 0 10px 0;
  font-size: 1.2rem;
  text-align: right;
  font-weight: 500;
}

.dhikr-card .translation {
  color: #2C2A2A;
  margin: 0 0 15px 0;
  font-size: 0.9rem;
  font-weight: 300;
}

.dhikr-card .count {
  color: #9C8466;
  font-weight: 700;
}

.custom-dhikr-form {
  background: rgba(240, 240, 240, .67);
  border-radius: 8px;
  padding: 20px;
  box-shadow: rgba(0, 0, 0, .25) 0 4px 4px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  color: #2C2A2A;
  margin-bottom: 8px;
  font-size: 0.9rem;
  font-weight: 500;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid #9C8466;
  border-radius: 8px;
  background: #ffffff;
  color: #2C2A2A;
  font-size: 1rem;
}

.form-control:focus {
  outline: none;
  border-color: #A79277;
}

.btn-primary {
  width: 100%;
  padding: 12px;
  background: #9C8466;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  background: #A79277;
  transform: translateY(-2px);
  box-shadow: rgba(149, 157, 165, 0.5) 0 8px 24px;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .carousel-slide {
    gap: 10px;
  }
  
  .dhikr-card {
    padding: 12px;
  }
  
  .dhikr-card h3 {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .counter-view { padding-top: 0; }
  .carousel-container {
    padding: 0;
  }

  .carousel-content {
    margin: 0 8px;
  }
  
  .counter-container {
    padding: 15px;
  }

  .counter-number {
    font-size: 48px;
  }

  .counter-text {
    font-size: 18px;
  }

  .counter-button {
    padding: 12px 24px;
    font-size: 16px;
  }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(240, 240, 240, .67);
  border-radius: 8px;
  padding: 40px 20px;
  text-align: center;
  box-shadow: rgba(0, 0, 0, .25) 0 4px 4px;
}

.empty-icon {
  color: #9C8466;
  margin-bottom: 16px;
  opacity: 0.7;
}

.empty-state h3 {
  color: #2C2A2A;
  font-size: 20px;
  margin-bottom: 8px;
  font-weight: 500;
}

.empty-state p {
  color: #666;
  font-size: 16px;
  max-width: 300px;
  margin: 0 auto;
}

.arabic-text {
  font-size: calc(1.5rem * var(--font-size-factor));
  line-height: calc(1.6 * var(--font-size-factor));
  text-align: center;
  margin: 10px 0;
  color: #2C2A2A;
}

/* Adhkars selection grid */
.adhkars-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin: 10px 0 20px;
}

@media (max-width: 768px) {
  .adhkars-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.dhikr-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 12px;
  background: rgba(240, 240, 240, .67);
  border: 1px solid #9C8466;
  border-radius: 10px;
  color: #2C2A2A;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
  min-height: 48px;
}

.dhikr-chip:hover {
  background: #f0ece6;
  transform: translateY(-1px);
  box-shadow: rgba(149, 157, 165, 0.3) 0 4px 12px;
}

.dhikr-chip.active {
  background: #9C8466;
  color: #ffffff;
}

.chip-title {
  font-weight: 600;
  font-size: 0.95rem;
}

body.dark-mode .dhikr-chip {
  background: #2a2a2a;
  border-color: #C5B192;
  color: #ddd;
}

body.dark-mode .dhikr-chip:hover {
  background: #333333;
}

body.dark-mode .dhikr-chip.active {
  background: #C5B192;
  color: #1a1a1a;
}

.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
  direction: rtl;
}

.toast {
  background-color: #fff;
  border-radius: 8px;
  padding: 12px 20px;
  margin-bottom: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
  min-width: 250px;
  max-width: 350px;
  transform-origin: top right;
}

.toast.success {
  background-color: #4caf50;
  color: white;
}

.toast.warning {
  background-color: #ff9800;
  color: white;
}

.toast.error {
  background-color: #f44336;
  color: white;
}

.toast-content {
  display: flex;
  align-items: center;
}

.toast-message {
  font-weight: 500;
}

.toast-enter-active, 
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from, 
.toast-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>