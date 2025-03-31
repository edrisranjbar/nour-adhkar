<style scoped>
.counter-aya {
  font-weight: 100;
  font-size: 20px;
  text-align: center;
  margin-top: 99px;
  color: #000000;
}

.counter-action-buttons {
  background-color: transparent;
  padding: 4px 16px;
  color: #2F2F2F;
  font-weight: 400;
  font-size: 20px;
  border-radius: 16px;
  margin-top: 32px;
  margin-left: 8px;
  border: 2px solid #9C8466;
  text-decoration: none;
  cursor: pointer;
}

.counter-view {
  min-height: 100vh;
  background: var(--background-color);
}

header {
  height: 80px;
  padding: 0 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--surface-color);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.counter-container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

section {
  margin-bottom: 40px;
}

h2 {
  color: var(--text-color);
  margin-bottom: 20px;
  font-size: 1.5rem;
}

.dhikr-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.dhikr-card {
  background: var(--surface-color);
  border-radius: 12px;
  padding: 20px;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dhikr-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.dhikr-card h3 {
  color: var(--text-color);
  margin: 0 0 10px 0;
  font-size: 1.2rem;
  text-align: right;
}

.dhikr-card .translation {
  color: var(--text-secondary);
  margin: 0 0 15px 0;
  font-size: 0.9rem;
}

.dhikr-card .count {
  color: var(--primary-color);
  font-weight: bold;
}

.custom-dhikr-form {
  background: var(--surface-color);
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  color: var(--text-color);
  margin-bottom: 8px;
  font-size: 0.9rem;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--background-color);
  color: var(--text-color);
  font-size: 1rem;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
}

.btn-primary {
  width: 100%;
  padding: 12px;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary:not(:disabled):hover {
  opacity: 0.9;
}

@media (max-width: 480px) {
  .counter-container {
    padding: 15px;
  }
  
  .dhikr-grid {
    grid-template-columns: 1fr;
  }
}
</style>
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
        <h2>اذکار پیش‌فرض</h2>
        <div class="dhikr-grid">
          <div v-for="dhikr in defaultDhikrs" 
               :key="dhikr.id" 
               class="dhikr-card"
               @click="selectDhikr(dhikr)">
            <h3>{{ dhikr.title }}</h3>
            <p class="translation">{{ dhikr.translation }}</p>
            <span class="count">{{ dhikr.count }} مرتبه</span>
          </div>
        </div>
      </section>

      <!-- Custom Dhikr Section -->
      <section class="custom-dhikr">
        <h2>اذکار سفارشی</h2>
        <div class="custom-dhikr-form">
          <div class="form-group">
            <label for="title">متن ذکر</label>
            <input type="text" 
                   id="title" 
                   v-model="customDhikr.title" 
                   placeholder="متن ذکر را وارد کنید"
                   class="form-control">
          </div>
          <div class="form-group">
            <label for="translation">ترجمه</label>
            <input type="text" 
                   id="translation" 
                   v-model="customDhikr.translation" 
                   placeholder="ترجمه را وارد کنید"
                   class="form-control">
          </div>
          <div class="form-group">
            <label for="count">تعداد</label>
            <input type="number" 
                   id="count" 
                   v-model="customDhikr.count" 
                   min="1"
                   class="form-control">
          </div>
          <button class="btn-primary" 
                  @click="createCustomDhikr"
                  :disabled="!isCustomDhikrValid">
            ایجاد ذکر جدید
          </button>
        </div>
      </section>
    </main>
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
      defaultDhikrs: [],
      customDhikr: {
        title: '',
        translation: '',
        count: 33
      }
    }
  },
  computed: {
    ...mapState(['user']),
    ...mapGetters(['isAuthenticated']),
    isCustomDhikrValid() {
      return this.customDhikr.title.trim() && 
             this.customDhikr.translation.trim() && 
             this.customDhikr.count > 0;
    }
  },
  methods: {
    async fetchDefaultDhikrs() {
      try {
        const response = await axios.get(`${BASE_API_URL}/default-dhikrs`, {
          headers: {
            Authorization: `Bearer ${this.$store.state.token}`
          }
        });
        this.defaultDhikrs = response.data;
      } catch (error) {
        console.error('Error fetching default dhikrs:', error);
      }
    },
    async createCustomDhikr() {
      if (!this.isCustomDhikrValid) return;
      
      try {
        const response = await axios.post(`${BASE_API_URL}/default-dhikrs`, 
          this.customDhikr,
          {
            headers: {
              Authorization: `Bearer ${this.$store.state.token}`
            }
          }
        );
        
        this.defaultDhikrs.push(response.data);
        this.resetCustomDhikr();
      } catch (error) {
        console.error('Error creating custom dhikr:', error);
      }
    },
    selectDhikr(dhikr) {
      // Navigate to DhikrView with the selected dhikr
      this.$router.push({
        name: 'dhikr',
        params: { id: dhikr.id },
        query: { 
          title: dhikr.title,
          text: dhikr.title,
          translation: dhikr.translation,
          count: dhikr.count
        }
      });
    },
    resetCustomDhikr() {
      this.customDhikr = {
        title: '',
        translation: '',
        count: 33
      };
    }
  },
  mounted() {
    if (this.isAuthenticated) {
      this.fetchDefaultDhikrs();
    }
  }
}
</script>