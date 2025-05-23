<template>
  <div class="donation-container">
    <AppHeader title="حمایت از ما" description="با حمایت شما، اذکار نور به رشد و توسعه ادامه می‌دهد" />
    
    <main class="donation-content">
      <section class="donation-section">
        <div class="intro-card">
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
          
          <div 
            class="amount-option"
            :class="{ 'active': showCustomInput }"
            @click="showCustomAmountInput"
          >
            <span class="amount-value">مبلغ دلخواه</span>
            <span class="amount-description">تعیین مبلغ دلخواه</span>
          </div>

          <div v-if="showCustomInput" class="amount-option custom-amount full-width">
            <input 
              type="text" 
              v-model="displayAmount"
              @input="selectCustomAmount"
              placeholder="مبلغ دلخواه"
              class="custom-amount-input"
              ref="customInput"
            />
            <span class="amount-description">تومان</span>
          </div>
        </div>

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
          </span>
        </button>
        
        <div class="secure-payment">
          <div class="secure-icon">
            <font-awesome-icon icon="fa-solid fa-lock" />
          </div>
          <p class="secure-text">پرداخت امن از طریق درگاه زرین‌پال</p>
        </div>

        <div class="recent-supporters" v-if="recentSupporters.length > 0">
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
import { onMounted, ref, computed, nextTick } from 'vue';

export default {
  name: 'DonationView',
  components: {
    AppHeader,
    AppFooter
  },
  setup() {
    const selectedAmount = ref(1000000);
    const customAmount = ref(null);
    const displayAmount = ref('۱,۰۰۰,۰۰۰');
    const isLoading = ref(false);
    const errorMessage = ref('');
    const showCustomInput = ref(false);
    const customInput = ref(null);
    const donationAmounts = [
      { value: 100000, label: '۱۰۰,۰۰۰', description: 'حمایت کوچک' },
      { value: 500000, label: '۵۰۰,۰۰۰', description: 'حمایت خوب' },
      { value: 1000000, label: '۱,۰۰۰,۰۰۰', description: 'حمایت متوسط' },
      { value: 5000000, label: '۵,۰۰۰,۰۰۰', description: 'حمایت برتر' },
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
      showCustomInput.value = false;
      displayAmount.value = donationAmounts.find(a => a.value === amount)?.label || formatAmount(amount);
    };
    
    const showCustomAmountInput = () => {
      showCustomInput.value = true;
      selectedAmount.value = null;
      customAmount.value = null;
      displayAmount.value = '';
      // Focus the input after the next tick to ensure it's rendered
      nextTick(() => {
        customInput.value?.focus();
      });
    };
    
    const selectCustomAmount = () => {
      if (displayAmount.value) {
        const numericValue = parseInt(displayAmount.value.toString().replace(/,/g, ''));
        if (!isNaN(numericValue) && numericValue > 0) {
          customAmount.value = numericValue;
          displayAmount.value = formatAmount(numericValue);
        }
      }
    };
    
    const formatAmount = (amount) => {
      if (amount === null || amount === undefined) return '۰';
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
      displayAmount,
      isLoading,
      errorMessage,
      donationAmounts,
      recentSupporters,
      finalAmount,
      canProceed,
      selectAmount,
      selectCustomAmount,
      formatAmount,
      proceedToDonation,
      showCustomInput,
      showCustomAmountInput,
      customInput
    };
  }
};
</script>

<style scoped>
.donation-container {
  min-height: 100vh;
  max-width: 1000px;
  margin: auto;
  display: flex;
  flex-direction: column;
}

.donation-content {
  padding: 16px;
  margin: 0 auto;
  flex: 1;
}

.donation-section {
  background-color: #fff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

body.dark-mode .donation-section {
  background-color: #262626;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
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

/* Intro Card */
.intro-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1.5rem;
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

/* Donation Amounts */
.donation-amounts {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.amount-option {
  background-color: rgba(167, 146, 119, 0.05);
  border-radius: 12px;
  padding: 1.25rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

body.dark-mode .amount-option {
  background-color: rgba(167, 146, 119, 0.1);
}

.amount-option:hover {
  transform: translateY(-3px);
}

.amount-option.active {
  border-color: #A79277;
  background-color: rgba(167, 146, 119, 0.1);
}

body.dark-mode .amount-option.active {
  background-color: rgba(167, 146, 119, 0.2);
}

.amount-value, .amount-description {
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
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
  background-color: rgba(167, 146, 119, 0.1);
  border: 2px solid #A79277;
  display: flex;
  flex-direction: row;
  align-items: center;
  padding: 1rem 1.5rem;
}

body.dark-mode .amount-option.full-width {
  background-color: rgba(167, 146, 119, 0.2);
  border: 2px solid #A79277;
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

/* Donation Summary */
.donation-summary {
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
  display: none;
}

/* Recent Supporters */
.recent-supporters {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(167, 146, 119, 0.2);
}

.supporters-list {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

.supporter-card {
  background-color: rgba(167, 146, 119, 0.05);
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  align-items: center;
}

body.dark-mode .supporter-card {
  background-color: rgba(167, 146, 119, 0.1);
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
  
  .intro-text {
    margin-left: 2rem;
  }
  
  .donation-amounts {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .supporters-list {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>