<template>
  <div class="donation-result-container">
    <AppHeader title="مشکل در پرداخت" description="پرداخت انجام نشد" />
    
    <main class="donation-result-content">
      <div class="result-card failed">
        <div class="result-icon">
          <font-awesome-icon icon="fa-solid fa-times" />
        </div>
        
        <h1 class="result-title">پرداخت ناموفق بود</h1>
        <p class="result-message">{{ errorMessage }}</p>
        
        <div class="result-info">
          <font-awesome-icon icon="fa-solid fa-info-circle" class="info-icon" />
          <p>
            وجه از حساب شما کسر نشده یا در صورت کسر شدن، به حساب شما برگشت داده خواهد شد.
          </p>
        </div>
        
        <div class="action-buttons">
          <router-link to="/donation" class="action-button primary">
            تلاش مجدد
            <font-awesome-icon icon="fa-solid fa-redo" class="button-icon" />
          </router-link>
          
          <router-link to="/" class="action-button secondary">
            بازگشت به خانه
            <font-awesome-icon icon="fa-solid fa-home" class="button-icon" />
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
  name: 'DonationFailedView',
  components: {
    AppHeader,
    AppFooter
  },
  setup() {
    const route = useRoute();
    const errorMessage = ref('متاسفانه پرداخت شما با مشکل مواجه شد.');
    
    onMounted(() => {
      if (route.query.message) {
        errorMessage.value = decodeURIComponent(route.query.message);
      }
    });
    
    return {
      errorMessage
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

.result-card.failed {
  border-top: 5px solid #dc3545;
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

.failed .result-icon {
  background-color: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}

body.dark-mode .failed .result-icon {
  background-color: rgba(220, 53, 69, 0.2);
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

.result-info {
  display: flex;
  gap: 1rem;
  background-color: rgba(0, 123, 255, 0.05);
  padding: 1.2rem;
  border-radius: 12px;
  width: 100%;
  margin-bottom: 2rem;
  text-align: right;
}

body.dark-mode .result-info {
  background-color: rgba(0, 123, 255, 0.1);
}

.info-icon {
  color: #0275d8;
  font-size: 1.5rem;
  flex-shrink: 0;
  margin-top: 0.2rem;
}

.result-info p {
  margin: 0;
  font-size: 0.95rem;
  line-height: 1.7;
  color: #555;
}

body.dark-mode .result-info p {
  color: #bbb;
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