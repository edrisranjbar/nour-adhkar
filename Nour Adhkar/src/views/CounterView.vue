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

      <!-- Default Dhikrs Section -->
      <section class="default-dhikrs">
        <div class="section-header">
          <h2>اذکار</h2>
          <button class="add-dhikr-button" @click="showCustomDhikrModal = true">
            <font-awesome-icon icon="fa-solid fa-plus" />
            افزودن ذکر
          </button>
        </div>
        <div v-if="adhkars.length > 0" class="dhikr-grid">
          <div v-for="dhikr in adhkars" :key="dhikr.id" class="dhikr-card"
            :class="{ 'active': currentDhikr.id === dhikr.id }" @click="selectDhikr(dhikr)">
            <h3>{{ dhikr.title }}</h3>
            <p class="translation">{{ dhikr.translation }}</p>
            <span class="count">{{ dhikr.count }} مرتبه</span>
          </div>
        </div>
        <div v-else class="empty-state">
          <div class="empty-icon">
            <font-awesome-icon icon="fa-solid fa-book-open" size="3x" />
          </div>
          <h3>هیچ ذکری یافت نشد</h3>
          <p>برای شروع، از دکمه «افزودن ذکر» استفاده کنید.</p>
        </div>
      </section>

      <div class="counter-display">
        <h2 v-if="currentDhikr.title">{{ currentDhikr.title }}</h2>
        <p v-if="currentDhikr.arabic_text" class="arabic-text">{{ currentDhikr.arabic_text }}</p>
        <p v-if="currentDhikr.translation" class="translation">{{ currentDhikr.translation }}</p>
        <div class="counter-number">{{ currentCount }}</div>
        <div class="counter-text">مرتبه</div>
        <div v-if="currentDhikr.count" class="progress-bar">
          <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
        </div>

        <div class="counter-controls">
          <button class="counter-button count-button" @click="incrementCount">
            شمارش
          </button>
          <button class="counter-button reset-button" @click="resetCounter">
            بازنشانی
          </button>

        </div>

      </div>

    </main>

    <!-- Custom Dhikr Modal -->
    <div v-if="showCustomDhikrModal" class="modal">
      <header>
        <div class="d-flex">
          <button class="appbar-action-button-wrapper" @click="showCustomDhikrModal = false">
            <img class="appbar-action-button" src="@/assets/icons/back-button.svg" alt="برگشتن">
          </button>
          <h1 id="modal-title">افزودن ذکر جدید</h1>
        </div>
      </header>

      <div class="modal-container">
        <div class="custom-dhikr-form">
          <div class="form-group">
            <label for="title">عنوان ذکر</label>
            <input type="text" id="title" v-model="customDhikr.title" placeholder="عنوان ذکر را وارد کنید"
              class="form-control">
          </div>
          <div class="form-group">
            <label for="arabic_text">متن عربی</label>
            <textarea id="arabic_text" v-model="customDhikr.arabic_text" placeholder="متن عربی ذکر را وارد کنید"
              class="form-control" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label for="translation">ترجمه فارسی</label>
            <input type="text" id="translation" v-model="customDhikr.translation" placeholder="ترجمه فارسی را وارد کنید"
              class="form-control">
          </div>
          <div class="form-group">
            <label for="prefix">پیشوند</label>
            <input type="text" id="prefix" v-model="customDhikr.prefix" placeholder="پیشوند ذکر (اختیاری)"
              class="form-control">
          </div>
          <div class="form-group">
            <label for="count">تعداد تکرار</label>
            <input type="number" id="count" v-model="customDhikr.count" min="1" class="form-control">
          </div>
          <button class="btn-primary" @click="createCustomDhikr" :disabled="!isCustomDhikrValid">
            ایجاد ذکر جدید
          </button>
        </div>
      </div>
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
      customDhikr: {
        title: '',
        translation: '',
        count: 33,
        arabic_text: '',
        prefix: ''
      },
      currentDhikr: {},
      currentCount: 0,
      hasCompleted: false,
      showCustomDhikrModal: false
    }
  },
  computed: {
    ...mapState(['user']),
    ...mapGetters(['isAuthenticated']),
    isCustomDhikrValid() {
      return this.customDhikr.title.trim() &&
        this.customDhikr.arabic_text.trim() &&
        this.customDhikr.translation.trim() &&
        this.customDhikr.count > 0;
    },
    progressPercentage() {
      if (!this.currentDhikr.count) return 0;
      return (this.currentCount / this.currentDhikr.count) * 100;
    }
  },
  methods: {
    async fetchAdhkars() {
      try {
        const response = await axios.get(`${BASE_API_URL}/adhkars`, {
          headers: {
            Authorization: `Bearer ${this.$store.state.token}`
          }
        });
        this.adhkars = response.data.adhkar;
      } catch (error) {
        console.error('Error fetching adhkars:', error);
      }
    },
    async createCustomDhikr() {
      if (!this.isCustomDhikrValid) return;

      try {
        const response = await axios.post(`${BASE_API_URL}/adhkars`,
          this.customDhikr,
          {
            headers: {
              Authorization: `Bearer ${this.$store.state.token}`
            }
          }
        );

        this.adhkars.push(response.data);
        this.resetCustomDhikr();
        this.showCustomDhikrModal = false;
      } catch (error) {
        console.error('Error creating custom dhikr:', error);
      }
    },
    selectDhikr(dhikr) {
      this.currentDhikr = dhikr;
      this.currentCount = 0;
      this.hasCompleted = false;
      this.resetCounter();
    },
    resetCustomDhikr() {
      this.customDhikr = {
        title: '',
        translation: '',
        count: 33,
        arabic_text: '',
        prefix: ''
      };
    },
    incrementCount() {
      if (!this.currentDhikr.id) {
        alert('لطفاً ابتدا یک ذکر انتخاب کنید');
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
      alert('تبریک! شما ذکر را به پایان رساندید.');
    }
  },
  mounted() {
    this.fetchAdhkars();
  }
}
</script>

<style scoped>
.counter-view {
  min-height: 100vh;
  background: #D1BB9E;
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
}

.counter-container {
  padding: 20px;
  max-width: 735px;
  margin: 0 auto;
}

.counter-display {
  text-align: center;
  margin: 40px 0;
  padding: 20px;
  background: rgba(240, 240, 240, .67);
  border-radius: 8px;
  box-shadow: rgba(0, 0, 0, .25) 0 4px 4px;
}

.counter-number {
  font-size: 72px;
  font-weight: 700;
  color: #9C8466;
  line-height: 1;
}

.counter-text {
  font-size: 24px;
  color: #2C2A2A;
  margin-top: 8px;
  font-weight: 300;
}

.counter-controls {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 32px;
  margin-bottom: 32px;
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

.dhikr-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.dhikr-card {
  background: rgba(240, 240, 240, .67);
  border-radius: 8px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: rgba(0, 0, 0, .25) 0 4px 4px;
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

@media (max-width: 480px) {
  .counter-container {
    padding: 15px;
  }

  .dhikr-grid {
    grid-template-columns: 1fr;
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
  font-family: 'Traditional Arabic', 'Scheherazade New', serif;
  font-size: 1.5rem;
  line-height: 1.6;
  text-align: center;
  margin: 10px 0;
  color: #2C2A2A;
}
</style>