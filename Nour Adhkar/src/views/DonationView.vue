<template>
  <div class="donation-container">
    <AppHeader title="حمایت از ما" description="با حمایت شما، اذکار نور به رشد و توسعه ادامه می‌دهد" />
    
    <main class="donation-content">
      <section class="donation-intro">
        <div class="intro-card">
          <div class="intro-icon">
            <font-awesome-icon icon="fa-solid fa-heart" />
          </div>
          <div class="intro-text">
            <h2>چرا حمایت کنیم؟</h2>
            <p>اذکار نور یک پروژه منبع باز و رایگان است که برای ترویج فرهنگ اسلامی و دسترسی آسان به اذکار و ادعیه ایجاد شده است. حمایت شما به ما کمک می‌کند تا:</p>
            <ul class="benefits-list">
              <li>سرور‌های بهتر و سریع‌تر تهیه کنیم</li>
              <li>ویژگی‌های جدید اضافه کنیم</li>
              <li>محتوای بیشتری را به مجموعه اضافه کنیم</li>
              <li>برنامه را در دسترس کاربران بیشتری قرار دهیم</li>
            </ul>
          </div>
        </div>
      </section>
      
      <section class="donation-options">
        <h2 class="section-title">مبلغ دلخواه خود را انتخاب کنید</h2>
        
        <div class="donation-amounts">
          <div 
            v-for="amount in donationAmounts" 
            :key="amount.value"
            :class="['amount-option', selectedAmount === amount.value ? 'active' : '']"
            @click="selectAmount(amount.value)"
          >
            <span class="amount-value">{{ amount.label }}</span>
            <span class="amount-description">{{ amount.description }}</span>
          </div>
          
          <div class="amount-option custom-amount full-width">
            <input 
              type="number" 
              v-model="customAmount" 
              @input="selectCustomAmount"
              placeholder="مبلغ دلخواه"
              class="custom-amount-input"
            />
            <span class="amount-description">تومان</span>
          </div>
        </div>
      </section>
      
      <section class="payment-methods">
        <h2 class="section-title">روش پرداخت</h2>
        
        <div class="payment-method-card">
          <div class="payment-header">
            <div class="payment-icon">
              <font-awesome-icon icon="fa-solid fa-credit-card" />
            </div>
            <h3 class="payment-title">درگاه زرین‌پال</h3>
          </div>
          <p class="payment-description">
            پرداخت امن و سریع از طریق درگاه پرداخت زرین‌پال
          </p>
        </div>
      </section>
      
      <section class="donation-action">
        <div class="donation-summary">
          <div class="summary-amount">
            <span class="summary-label">مبلغ حمایت:</span>
            <span class="summary-value">{{ formatAmount(finalAmount) }} <span class="currency">تومان</span></span>
          </div>
        </div>
        
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
        
        <button 
          class="donate-button" 
          :disabled="!canProceed"
          @click="proceedToDonation"
        >
          <span v-if="isLoading">در حال اتصال به درگاه...</span>
          <span v-else>
            پرداخت و حمایت
            <font-awesome-icon icon="fa-solid fa-heart" class="button-icon" />
          </span>
        </button>
        
        <div class="secure-payment">
          <div class="secure-icon">
            <font-awesome-icon icon="fa-solid fa-lock" />
          </div>
          <p class="secure-text">تمام تراکنش‌ها از طریق درگاه‌های امن پرداخت انجام می‌شوند</p>
        </div>
      </section>
      
      <section class="recent-supporters" v-if="recentSupporters.length > 0">
        <h2 class="section-title">حامیان اخیر</h2>
        
        <div class="supporters-list">
          <div v-for="(supporter, index) in recentSupporters" :key="index" class="supporter-card">
            <div class="supporter-avatar">
              {{ supporter.name.substring(0, 1) }}
            </div>
            <div class="supporter-info">
              <h3 class="supporter-name">{{ supporter.name }}</h3>
              <p class="supporter-amount">{{ formatAmount(supporter.amount) }} تومان</p>
              <p class="supporter-date">{{ supporter.date }}</p>
            </div>
          </div>
        </div>
      </section>
    </main>
    
    <AppFooter />
  </div>
</template>

<script>
import AppHeader from '@/components/Header.vue';
import AppFooter from '@/components/Footer.vue';
import { donationService } from '@/services/donationService';
import { onMounted, ref, computed } from 'vue';

export default {
  name: 'DonationView',
  components: {
    AppHeader,
    AppFooter
  },
  setup() {
    const selectedAmount = ref(500000);
    const customAmount = ref(null);
    const isLoading = ref(false);
    const errorMessage = ref('');
    const donationAmounts = [
      { value: 100000, label: '۱۰۰,۰۰۰', description: 'حمایت کوچک' },
      { value: 500000, label: '۵۰۰,۰۰۰', description: 'حمایت خوب' },
      { value: 1000000, label: '۱,۰۰۰,۰۰۰', description: 'حمایت متوسط' },
      { value: 10000000, label: '۱۰,۰۰۰,۰۰۰', description: 'حمایت عالی' }
    ];
    const recentSupporters = ref([]);
    
    const finalAmount = computed(() => {
      return customAmount.value !== null ? customAmount.value : selectedAmount.value;
    });
    
    const canProceed = computed(() => {
      return finalAmount.value > 0 && !isLoading.value;
    });
    
    const selectAmount = (amount) => {
      selectedAmount.value = amount;
      customAmount.value = null;
    };
    
    const selectCustomAmount = () => {
      if (customAmount.value && customAmount.value > 0) {
        selectedAmount.value = null;
      } else if (customAmount.value === null || customAmount.value === '') {
        selectedAmount.value = 500000;
      }
    };
    
    const formatAmount = (amount) => {
      return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };
    
    const loadRecentDonations = async () => {
      try {
        const donations = await donationService.getRecentDonations();
        if (donations.length > 0) {
          recentSupporters.value = donations;
        }
      } catch (error) {
        console.error('Failed to load recent donations:', error);
      }
    };
    
    const proceedToDonation = async () => {
      if (!canProceed.value) return;
      
      isLoading.value = true;
      errorMessage.value = '';
      
      try {
        const result = await donationService.createDonation({
          amount: finalAmount.value,
          // You can add name/email collection in the form if needed
        });
        
        if (result.success) {
          // Redirect to Zarinpal
          window.location.href = result.redirectUrl;
        } else {
          errorMessage.value = result.message || 'خطا در اتصال به درگاه پرداخت. لطفا مجددا تلاش کنید.';
        }
      } catch (error) {
        errorMessage.value = 'خطا در ارتباط با سرور. لطفا مجددا تلاش کنید.';
        console.error('Payment initiation error:', error);
      } finally {
        isLoading.value = false;
      }
    };
    
    onMounted(() => {
      loadRecentDonations();
    });
    
    return {
      selectedAmount,
      customAmount,
      isLoading,
      errorMessage,
      donationAmounts,
      recentSupporters,
      finalAmount,
      canProceed,
      selectAmount,
      selectCustomAmount,
      formatAmount,
      proceedToDonation
    };
  }
};
</script>

<style scoped>
.donation-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.donation-content {
  padding: 16px;
  max-width: 800px;
  margin: 0 auto;
  flex: 1;
}

.section-title {
  font-size: 1.5rem;
  color: #333;
  margin-bottom: 1.5rem;
  position: relative;
  padding-right: 1rem;
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

/* Intro Section */
.donation-intro {
  margin-bottom: 2rem;
}

.intro-card {
  background-color: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

body.dark-mode .intro-card {
  background-color: #262626;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.intro-icon {
  width: 60px;
  height: 60px;
  aspect-ratio: 1 / 1;
  background-color: #A79277;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
}

.intro-text h2 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: #333;
}

body.dark-mode .intro-text h2 {
  color: #eee;
}

.intro-text p {
  font-size: 1rem;
  line-height: 1.6;
  margin-bottom: 1rem;
  color: #555;
}

body.dark-mode .intro-text p {
  color: #bbb;
}

.benefits-list {
  padding-right: 1.2rem;
  text-align: right;
}

.benefits-list li {
  margin-bottom: 0.75rem;
  color: #555;
}

body.dark-mode .benefits-list li {
  color: #bbb;
}

/* Donation Options */
.donation-options, .payment-methods, .donation-action, .recent-supporters {
  margin-bottom: 2.5rem;
}

.donation-amounts {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.amount-option {
  background-color: #fff;
  border-radius: 12px;
  padding: 1.25rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

body.dark-mode .amount-option {
  background-color: #262626;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.amount-option:hover {
  transform: translateY(-3px);
}

.amount-option.active {
  border-color: #A79277;
  background-color: rgba(167, 146, 119, 0.05);
}

body.dark-mode .amount-option.active {
  background-color: rgba(167, 146, 119, 0.15);
}

.amount-value {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
}

body.dark-mode .amount-value {
  color: #eee;
}

.amount-description {
  font-size: 0.9rem;
  color: #777;
}

body.dark-mode .amount-description {
  color: #aaa;
}

.amount-option.full-width {
  grid-column: 1 / -1;
  margin-top: 1rem;
  background-color: rgba(167, 146, 119, 0.05);
  border: 2px dashed rgba(167, 146, 119, 0.3);
  display: flex;
  flex-direction: row;
  align-items: center;
  padding: 1rem 1.5rem;
}

body.dark-mode .amount-option.full-width {
  background-color: rgba(167, 146, 119, 0.1);
  border: 2px dashed rgba(167, 146, 119, 0.4);
}

.custom-amount-input {
  width: 100%;
  padding: 0.75rem;
  text-align: center;
  border: none;
  background-color: transparent;
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  outline: none;
  font-family: inherit;
}

body.dark-mode .custom-amount-input {
  color: #eee;
}

.custom-amount-input::placeholder {
  color: #aaa;
}

/* Payment Methods */
.payment-method-card {
  background-color: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
  border-left: 4px solid #A79277;
}

body.dark-mode .payment-method-card {
  background-color: #262626;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.payment-header {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.payment-icon {
  width: 50px;
  height: 50px;
  background-color: rgba(167, 146, 119, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: 1rem;
  color: #A79277;
  font-size: 1.5rem;
}

.payment-title {
  font-size: 1.2rem;
  color: #333;
  margin: 0;
}

body.dark-mode .payment-title {
  color: #eee;
}

.payment-description {
  color: #555;
  margin: 0;
  line-height: 1.6;
}

body.dark-mode .payment-description {
  color: #bbb;
}

/* Donation Action */
.donation-action {
  background-color: #fff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

body.dark-mode .donation-action {
  background-color: #262626;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.donation-summary {
  margin-bottom: 2rem;
  width: 100%;
  background-color: rgba(167, 146, 119, 0.05);
  padding: 1.5rem;
  border-radius: 12px;
}

body.dark-mode .donation-summary {
  background-color: rgba(167, 146, 119, 0.1);
}

.summary-amount {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.summary-label {
  font-size: 1.1rem;
  color: #555;
}

body.dark-mode .summary-label {
  color: #bbb;
}

.summary-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #A79277;
}

.currency {
  font-size: 1rem;
  font-weight: 400;
  color: #777;
  margin-right: 0.25rem;
}

body.dark-mode .currency {
  color: #aaa;
}

.secure-payment {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 1rem;
}

.secure-icon {
  color: #A79277;
  opacity: 0.8;
}

.secure-text {
  font-size: 0.9rem;
  color: #777;
  margin: 0;
}

body.dark-mode .secure-text {
  color: #aaa;
}

.error-message {
  background-color: rgba(220, 53, 69, 0.1);
  color: #dc3545;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  text-align: center;
  width: 100%;
}

body.dark-mode .error-message {
  background-color: rgba(220, 53, 69, 0.2);
}

.donate-button {
  background-color: #A79277;
  color: #fff;
  border: none;
  border-radius: 50px;
  padding: 1rem 2.5rem;
  font-size: 1.2rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 15px rgba(167, 146, 119, 0.3);
  min-width: 220px;
}

.donate-button:hover:not(:disabled) {
  background-color: #9C8466;
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(167, 146, 119, 0.4);
}

.donate-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.button-icon {
  margin-right: 0.75rem;
}

/* Recent Supporters */
.supporters-list {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

.supporter-card {
  background-color: #fff;
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  align-items: center;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

body.dark-mode .supporter-card {
  background-color: #262626;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.supporter-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: #A79277;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 600;
  margin-left: 1rem;
}

.supporter-info {
  flex: 1;
}

.supporter-name {
  font-size: 1rem;
  color: #333;
  margin: 0 0 0.25rem 0;
}

body.dark-mode .supporter-name {
  color: #eee;
}

.supporter-amount {
  font-size: 0.9rem;
  color: #A79277;
  font-weight: 600;
  margin: 0 0 0.25rem 0;
}

.supporter-date {
  font-size: 0.8rem;
  color: #777;
  margin: 0;
}

body.dark-mode .supporter-date {
  color: #aaa;
}

/* Responsive Styles */
@media (min-width: 768px) {
  .donation-content {
    padding: 24px;
  }
  
  .intro-card {
    flex-direction: row;
    text-align: right;
    padding: 2rem;
  }
  
  .intro-icon {
    margin-bottom: 0;
    margin-left: 2rem;
  }
  
  .donation-amounts {
    grid-template-columns: repeat(4, 1fr);
  }
  
  .supporters-list {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>