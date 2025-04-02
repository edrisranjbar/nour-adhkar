<template>
  <div class="donation-result-container">
    <AppHeader title="حمایت موفق" description="با تشکر از حمایت شما" />
    
    <main class="donation-result-content">
      <div class="result-card success">
        <div class="result-icon">
          <font-awesome-icon icon="fa-solid fa-check" />
        </div>
        
        <h1 class="result-title">پرداخت با موفقیت انجام شد</h1>
        <p class="result-message">با تشکر از حمایت شما از پروژه اذکار نور</p>
        
        <div class="result-details" v-if="referenceId">
          <p class="detail-row">
            <span class="detail-label">کد پیگیری:</span>
            <span class="detail-value">{{ referenceId }}</span>
          </p>
          <p class="note">
            لطفا این کد را برای پیگیری‌های بعدی نزد خود نگه دارید.
          </p>
        </div>
        
        <div class="action-buttons">
          <router-link to="/" class="action-button primary">
            بازگشت به خانه
            <font-awesome-icon icon="fa-solid fa-home" class="button-icon" />
          </router-link>
          
          <router-link to="/donation" class="action-button secondary">
            حمایت مجدد
            <font-awesome-icon icon="fa-solid fa-heart" class="button-icon" />
          </router-link>
        </div>
      </div>
    </main>
    
    <AppFooter />
  </div>
</template>

<script>
import AppHeader from '@/components/Header.vue';
import AppFooter from '@/components/Footer.vue';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

export default {
  name: 'DonationSuccessView',
  components: {
    AppHeader,
    AppFooter
  },
  setup() {
    const route = useRoute();
    const referenceId = ref('');
    
    onMounted(() => {
      if (route.query.reference) {
        referenceId.value = route.query.reference;
      }
    });
    
    return {
      referenceId
    };
  }
};
</script>

<style scoped>
.donation-result-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.donation-result-content {
  padding: 16px;
  max-width: 800px;
  margin: 0 auto;
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.result-card {
  background-color: #fff;
  border-radius: 16px;
  padding: 2.5rem 2rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  width: 100%;
  max-width: 600px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

body.dark-mode .result-card {
  background-color: #262626;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.result-card.success {
  border-top: 5px solid #28a745;
}

.result-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  margin-bottom: 1.5rem;
}

.success .result-icon {
  background-color: rgba(40, 167, 69, 0.1);
  color: #28a745;
}

body.dark-mode .success .result-icon {
  background-color: rgba(40, 167, 69, 0.2);
}

.result-title {
  font-size: 1.5rem;
  color: #333;
  margin-bottom: 1rem;
}

body.dark-mode .result-title {
  color: #eee;
}

.result-message {
  font-size: 1.1rem;
  color: #555;
  margin-bottom: 2rem;
}

body.dark-mode .result-message {
  color: #bbb;
}

.result-details {
  background-color: rgba(167, 146, 119, 0.05);
  padding: 1.5rem;
  border-radius: 12px;
  width: 100%;
  margin-bottom: 2rem;
}

body.dark-mode .result-details {
  background-color: rgba(167, 146, 119, 0.1);
}

.detail-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.detail-label {
  font-size: 1rem;
  color: #555;
}

body.dark-mode .detail-label {
  color: #bbb;
}

.detail-value {
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  letter-spacing: 1px;
}

body.dark-mode .detail-value {
  color: #eee;
}

.note {
  font-size: 0.9rem;
  color: #777;
  margin-top: 0.5rem;
}

body.dark-mode .note {
  color: #aaa;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  width: 100%;
  gap: 1rem;
}

.action-button {
  padding: 1rem;
  border-radius: 50px;
  font-size: 1.1rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  transition: all 0.3s ease;
}

.action-button.primary {
  background-color: #A79277;
  color: #fff;
  box-shadow: 0 4px 15px rgba(167, 146, 119, 0.3);
}

.action-button.primary:hover {
  background-color: #9C8466;
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(167, 146, 119, 0.4);
}

.action-button.secondary {
  background-color: rgba(167, 146, 119, 0.1);
  color: #A79277;
  border: 2px solid rgba(167, 146, 119, 0.3);
}

.action-button.secondary:hover {
  background-color: rgba(167, 146, 119, 0.2);
  transform: translateY(-3px);
}

.button-icon {
  margin-right: 0.75rem;
}

@media (min-width: 768px) {
  .donation-result-content {
    padding: 24px;
  }
  
  .action-buttons {
    flex-direction: row;
  }
}
</style> 