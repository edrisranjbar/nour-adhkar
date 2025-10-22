<template>
  <div class="counter-view">
    <!-- Custom Header -->
    <header>
      <div class="d-flex">
        <RouterLink to="/" class="d-flex align-items-center">
          <img class="appbar-action-button" src="@/assets/icons/back-button.svg" alt="برگشتن">
        </RouterLink>
        <h1 id="modal-title">شمارنده اذکار</h1>
      </div>
    </header>

    <!-- Main Counter Display -->
    <main class="counter-main">
      <!-- Counter Circle -->
      <div class="counter-container" @click="incrementCount">
        <div class="counter-circle" :class="{ 'completed': isCompleted }">
          <div class="counter-number">{{ currentCount }}</div>
          <div class="counter-label">مرتبه</div>
        </div>
        
        <!-- Progress Ring -->
        <svg class="progress-ring" :width="ringSize" :height="ringSize">
          <circle
            class="progress-ring-background"
            stroke="#E5E7EB"
            stroke-width="8"
            fill="transparent"
            :r="ringRadius"
            :cx="ringCenter"
            :cy="ringCenter"
          />
          <circle
            class="progress-ring-fill"
            stroke="#10B981"
            stroke-width="8"
            fill="transparent"
            :r="ringRadius"
            :cx="ringCenter"
            :cy="ringCenter"
            :stroke-dasharray="circumference"
            :stroke-dashoffset="strokeDashoffset"
            stroke-linecap="round"
          />
        </svg>
      </div>

      <!-- Goal Display -->
      <div class="goal-display">
        <div class="goal-info">
          <span class="goal-text">هدف:</span>
          <span class="goal-value">{{ goalText }}</span>
        </div>
        <div v-if="goal > 0" class="progress-text">
          {{ currentCount }} از {{ goal }}
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="action-buttons">
        <button class="action-btn reset-btn" @click="resetCounter">
          <font-awesome-icon icon="fa-solid fa-refresh" />
          <span>بازنشانی</span>
        </button>
        <button class="action-btn goal-btn" @click="showGoalModal = true">
          <font-awesome-icon icon="fa-solid fa-bullseye" />
          <span>تنظیم هدف</span>
        </button>
      </div>

      <!-- Completion Celebration -->
      <div v-if="isCompleted && showCelebration" class="celebration">
        <div class="celebration-content">
          <font-awesome-icon icon="fa-solid fa-star" class="celebration-icon" />
          <h3>تبریک!</h3>
          <p>شما به هدف خود رسیدید</p>
        </div>
      </div>
    </main>

    <!-- Goal Setting Modal -->
    <div v-if="showGoalModal" class="modal-overlay" @click="showGoalModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>تنظیم هدف</h3>
          <button class="close-btn" @click="showGoalModal = false">
            <font-awesome-icon icon="fa-solid fa-times" />
          </button>
        </div>
        <div class="modal-body">
          <div class="goal-options">
            <button 
              class="goal-option" 
              :class="{ active: goal === 0 }"
              @click="setGoal(0)"
            >
              <font-awesome-icon icon="fa-solid fa-infinity" />
              <span>بی‌نهایت</span>
            </button>
            <button 
              class="goal-option" 
              :class="{ active: goal === 33 }"
              @click="setGoal(33)"
            >
              <span class="goal-number">33</span>
              <span>تسبیح</span>
            </button>
            <button 
              class="goal-option" 
              :class="{ active: goal === 100 }"
              @click="setGoal(100)"
            >
              <span class="goal-number">100</span>
              <span>صد مرتبه</span>
            </button>
            <button 
              class="goal-option" 
              :class="{ active: goal === 1000 }"
              @click="setGoal(1000)"
            >
              <span class="goal-number">1000</span>
              <span>هزار مرتبه</span>
            </button>
          </div>
          <div class="custom-goal">
            <label>هدف سفارشی:</label>
            <input 
              type="number" 
              v-model="customGoal" 
              placeholder="عدد مورد نظر"
              min="1"
              max="9999"
            />
            <button 
              class="apply-custom-btn" 
              @click="setCustomGoal"
              :disabled="!customGoal || customGoal < 1"
            >
              اعمال
            </button>
          </div>
        </div>
      </div>
    </div>

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
import { useSettingsStore } from '@/stores/settings';
import tapSound from "@/assets/audios/click.mp3";

export default {
  name: 'CounterView',
  data() {
    return {
      currentCount: 0,
      goal: 0, // 0 means infinite
      customGoal: '',
      showGoalModal: false,
      showCelebration: false,
      toasts: [],
      celebrationTimeout: null,
      windowWidth: window.innerWidth
    }
  },
  computed: {
    ...mapState(['user']),
    ...mapGetters(['isAuthenticated']),
    isCompleted() {
      return this.goal > 0 && this.currentCount >= this.goal;
    },
    goalText() {
      if (this.goal === 0) return 'بی‌نهایت';
      return this.goal.toString();
    },
    progressPercentage() {
      if (this.goal === 0) return 0;
      return Math.min((this.currentCount / this.goal) * 100, 100);
    },
    ringSize() {
      // Responsive ring size based on screen width
      if (this.windowWidth >= 1440) return 400; // Extra Large Desktop
      if (this.windowWidth >= 1024) return 360; // Desktop
      if (this.windowWidth >= 769) return 320;  // Tablet
      if (this.windowWidth >= 481) return 240;  // Mobile
      return 200; // Small Mobile
    },
    ringRadius() {
      // Responsive radius based on screen width
      if (this.windowWidth >= 1440) return 192; // Extra Large Desktop
      if (this.windowWidth >= 1024) return 172; // Desktop
      if (this.windowWidth >= 769) return 152;  // Tablet
      if (this.windowWidth >= 481) return 112;  // Mobile
      return 92; // Small Mobile
    },
    ringCenter() {
      return this.ringSize / 2;
    },
    circumference() {
      return 2 * Math.PI * this.ringRadius;
    },
    strokeDashoffset() {
      const progress = this.progressPercentage / 100;
      return this.circumference - (progress * this.circumference);
    }
  },
  methods: {
    incrementCount() {
      this.currentCount++;
      this.playTapSound();
      this.vibrate();
      
      // Check if goal is reached
      if (this.isCompleted && !this.showCelebration) {
        this.showCelebration = true;
        this.addToast('تبریک! شما به هدف خود رسیدید', 'success');
        
        // Auto-hide celebration after 3 seconds and reset counter
        this.celebrationTimeout = setTimeout(() => {
          this.showCelebration = false;
          this.resetCounter();
        }, 3000);
      }
    },
    resetCounter() {
      this.currentCount = 0;
      this.showCelebration = false;
      if (this.celebrationTimeout) {
        clearTimeout(this.celebrationTimeout);
      }
      this.addToast('شمارنده بازنشانی شد', 'info');
    },
    setGoal(goalValue) {
      this.goal = goalValue;
      this.showGoalModal = false;
      this.currentCount = 0;
      this.showCelebration = false;
      this.addToast('هدف تنظیم شد', 'success');
    },
    setCustomGoal() {
      if (this.customGoal && this.customGoal > 0) {
        this.setGoal(parseInt(this.customGoal));
        this.customGoal = '';
      }
    },
    playTapSound() {
      const settings = useSettingsStore();
      if (settings.sound) {
        const audio = new Audio(tapSound);
        audio.play().catch(e => console.error('Audio play failed:', e));
      }
    },
    vibrate() {
      const settings = useSettingsStore();
      if (settings.vibration && 'vibrate' in navigator) {
        navigator.vibrate(50);
      }
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
    },
    handleResize() {
      this.windowWidth = window.innerWidth;
    }
  },
  mounted() {
    // Load saved goal from localStorage
    const savedGoal = localStorage.getItem('dhikr-counter-goal');
    if (savedGoal !== null) {
      this.goal = parseInt(savedGoal);
    }
    
    // Load saved count from localStorage
    const savedCount = localStorage.getItem('dhikr-counter-count');
    if (savedCount !== null) {
      this.currentCount = parseInt(savedCount);
    }
    
    // Add window resize listener
    window.addEventListener('resize', this.handleResize);
  },
  watch: {
    currentCount(newCount) {
      localStorage.setItem('dhikr-counter-count', newCount.toString());
    },
    goal(newGoal) {
      localStorage.setItem('dhikr-counter-goal', newGoal.toString());
    }
  },
  beforeUnmount() {
    if (this.celebrationTimeout) {
      clearTimeout(this.celebrationTimeout);
    }
    // Remove window resize listener
    window.removeEventListener('resize', this.handleResize);
  }
}
</script>

<style scoped>
/* Modern Mobile-First Counter View */
.counter-view {
  min-height: 100vh;
  max-width: 1000px;
  margin: auto;
  background: #D1BB9E;
  padding-top: 20px;
}

/* Dark mode support */
body.dark-mode .counter-view {
  background: #262626;
}

/* Header */
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

.d-flex {
  display: flex;
}

.align-items-center {
  align-items: center;
}

.appbar-action-button {
  width: 24px;
  height: 24px;
  margin-right: 16px;
}

/* Main Counter */
.counter-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1.5rem;
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

.counter-container {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.counter-container:hover {
  transform: scale(1.02);
}

.counter-container:active {
  transform: scale(0.98);
}

.counter-circle {
  width: 200px;
  height: 200px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  position: relative;
  z-index: 2;
}

.counter-circle.completed {
  background: linear-gradient(135deg, #10B981, #059669);
  color: white;
  animation: celebration 0.6s ease-in-out;
}

/* Dark mode counter circle */
body.dark-mode .counter-circle {
  background: rgba(31, 41, 55, 0.95);
  color: #E5E7EB;
}

body.dark-mode .counter-circle.completed {
  background: linear-gradient(135deg, #059669, #047857);
}

.counter-number {
  font-size: 3.5rem;
  font-weight: 700;
  line-height: 1;
  color: #1F2937;
  transition: all 0.3s ease;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

.counter-circle.completed .counter-number {
  color: white;
}

.counter-label {
  font-size: 1rem;
  font-weight: 500;
  color: #6B7280;
  margin-top: 0.5rem;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

.counter-circle.completed .counter-label {
  color: rgba(255, 255, 255, 0.9);
}

/* Dark mode text colors */
body.dark-mode .counter-number {
  color: #E5E7EB;
}

body.dark-mode .counter-label {
  color: #9CA3AF;
}

body.dark-mode .counter-circle.completed .counter-number {
  color: white;
}

body.dark-mode .counter-circle.completed .counter-label {
  color: rgba(255, 255, 255, 0.9);
}

.progress-ring {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 1;
}

.progress-ring-background {
  opacity: 0.3;
}

.progress-ring-fill {
  transition: stroke-dashoffset 0.5s ease;
  transform: rotate(-90deg);
  transform-origin: 50% 50%;
}

/* Dark mode progress ring */
body.dark-mode .progress-ring-background {
  stroke: #374151;
}

body.dark-mode .progress-ring-fill {
  stroke: #10B981;
}

/* Goal Display */
.goal-display {
  text-align: center;
  color: white;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

.goal-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.goal-text {
  font-size: 1.1rem;
  font-weight: 500;
  opacity: 0.9;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

.goal-value {
  font-size: 1.2rem;
  font-weight: 700;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

.progress-text {
  font-size: 0.9rem;
  opacity: 0.8;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

/* Dark mode goal display */
body.dark-mode .goal-display {
  color: #E5E7EB;
}

body.dark-mode .goal-text {
  color: #D1D5DB;
}

body.dark-mode .goal-value {
  background: rgba(55, 65, 81, 0.5);
  color: #F3F4F6;
}

body.dark-mode .progress-text {
  color: #9CA3AF;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 1rem;
  width: 100%;
  max-width: 400px;
  margin-bottom: 4rem;
}

.action-btn {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 16px;
  color: white;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.action-btn:active {
  transform: translateY(0);
}

.reset-btn:hover {
  background: rgba(239, 68, 68, 0.3);
}

.goal-btn:hover {
  background: rgba(16, 185, 129, 0.3);
}

/* Dark mode action buttons */
body.dark-mode .action-btn {
  background: rgba(55, 65, 81, 0.3);
  color: #E5E7EB;
}

body.dark-mode .action-btn:hover {
  background: rgba(75, 85, 99, 0.4);
}

body.dark-mode .reset-btn:hover {
  background: rgba(239, 68, 68, 0.3);
}

body.dark-mode .goal-btn:hover {
  background: rgba(16, 185, 129, 0.3);
}

/* Celebration */
.celebration {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

.celebration-content {
  text-align: center;
  color: white;
  animation: celebration 0.6s ease-in-out;
}

.celebration-icon {
  font-size: 4rem;
  color: #FCD34D;
  margin-bottom: 1rem;
  animation: bounce 1s infinite;
}

.celebration h3 {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

.celebration p {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

/* Goal Setting Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
  animation: fadeIn 0.3s ease;
}

.modal-content {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 400px;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideUp 0.3s ease;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem;
  border-bottom: 1px solid #E5E7EB;
}

.modal-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1F2937;
  margin: 0;
}

.close-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: #F3F4F6;
  border: none;
  border-radius: 8px;
  color: #6B7280;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #E5E7EB;
  color: #374151;
}

.modal-body {
  padding: 1.5rem;
}

.goal-options {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.goal-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1.5rem 1rem;
  background: #F9FAFB;
  border: 2px solid #E5E7EB;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
  font-weight: 500;
  color: #374151;
}

.goal-option:hover {
  background: #F3F4F6;
  border-color: #D1D5DB;
}

.goal-option.active {
  background: #10B981;
  border-color: #10B981;
  color: white;
}

.goal-number {
  font-size: 1.5rem;
  font-weight: 700;
}

.custom-goal {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.custom-goal label {
  font-size: 0.9rem;
  font-weight: 500;
  color: #374151;
}

.custom-goal input {
  padding: 0.75rem;
  border: 2px solid #E5E7EB;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s ease;
}

.custom-goal input:focus {
  outline: none;
  border-color: #10B981;
}

.apply-custom-btn {
  padding: 0.75rem;
  background: #10B981;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.apply-custom-btn:hover:not(:disabled) {
  background: #059669;
}

.apply-custom-btn:disabled {
  background: #D1D5DB;
  cursor: not-allowed;
}

/* Toast Notifications */
.toast-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 1000;
  direction: rtl;
}

.toast {
  background: white;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin-bottom: 0.75rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  min-width: 250px;
  max-width: 350px;
  transform-origin: top right;
  border-left: 4px solid #10B981;
}

.toast.success {
  border-left-color: #10B981;
}

.toast.warning {
  border-left-color: #F59E0B;
}

.toast.error {
  border-left-color: #EF4444;
}

.toast.info {
  border-left-color: #3B82F6;
}

.toast-content {
  display: flex;
  align-items: center;
}

.toast-message {
  font-weight: 500;
  color: #374151;
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

/* Animations */
@keyframes celebration {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
  40% { transform: translateY(-10px); }
  60% { transform: translateY(-5px); }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { 
    opacity: 0;
    transform: translateY(20px);
  }
  to { 
    opacity: 1;
    transform: translateY(0);
  }
}

/* Large screens (Desktop) */
@media (min-width: 1024px) {
  .counter-main {
    padding: 3rem 2rem;
    gap: 3rem;
  }
  
  .counter-circle {
    width: 280px;
    height: 280px;
  }
  
  .counter-number {
    font-size: 4.5rem;
  }
  
  .counter-label {
    font-size: 1.2rem;
  }
  
  .action-buttons {
    max-width: 500px;
  }
  
  .action-btn {
    padding: 1.5rem;
    font-size: 1rem;
  }
  
  .goal-display {
    font-size: 1.1rem;
  }
  
  .goal-value {
    font-size: 1.4rem;
    padding: 0.5rem 1rem;
  }
}

/* Extra large screens (Large Desktop) */
@media (min-width: 1440px) {
  .counter-main {
    padding: 4rem 3rem;
    gap: 4rem;
  }
  
  .counter-circle {
    width: 320px;
    height: 320px;
  }
  
  .counter-number {
    font-size: 5rem;
  }
  
  .counter-label {
    font-size: 1.4rem;
  }
  
  .action-buttons {
    max-width: 600px;
  }
  
  .action-btn {
    padding: 1.75rem;
    font-size: 1.1rem;
  }
  
  .goal-display {
    font-size: 1.2rem;
  }
  
  .goal-value {
    font-size: 1.6rem;
    padding: 0.75rem 1.25rem;
  }
}

/* Medium screens (Tablet) */
@media (min-width: 769px) and (max-width: 1023px) {
  .counter-main {
    padding: 2.5rem 2rem;
    gap: 2.5rem;
  }
  
  .counter-circle {
    width: 240px;
    height: 240px;
  }
  
  .counter-number {
    font-size: 4rem;
  }
  
  
  .action-buttons {
    max-width: 450px;
  }
  
  .action-btn {
    padding: 1.25rem;
  }
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .counter-main {
    padding: 1.5rem 1rem;
    gap: 1.5rem;
    min-height: calc(100vh - 80px);
  }
  
  .counter-circle {
    width: 180px;
    height: 180px;
  }
  
  .counter-number {
    font-size: 3rem;
  }
  
  .goal-display {
    margin-top: 2rem;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .action-btn {
    flex-direction: row;
    justify-content: center;
  }
  
  .modal-content {
    margin: 1rem;
    border-radius: 16px;
  }
  
  .goal-options {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .counter-main {
    padding: 1rem 0.5rem;
    gap: 1rem;
  }
  
  .counter-circle {
    width: 160px;
    height: 160px;
  }
  
  .counter-number {
    font-size: 2.5rem;
  }
  
  .counter-label {
    font-size: 0.9rem;
  }
  
  .goal-display {
    margin-top: 2.5rem;
  }
  
  .toast-container {
    top: 0.5rem;
    right: 0.5rem;
    left: 0.5rem;
  }
  
  .toast {
    min-width: auto;
    max-width: none;
  }
}

/* Dark mode modal and toast support */
body.dark-mode .modal-content {
  background: #1F2937;
  color: white;
}

body.dark-mode .modal-header {
  border-bottom-color: #374151;
}

body.dark-mode .modal-header h3 {
  color: white;
}

body.dark-mode .goal-option {
  background: #374151;
  border-color: #4B5563;
  color: white;
}

body.dark-mode .goal-option:hover {
  background: #4B5563;
}

body.dark-mode .goal-option.active {
  background: #10B981;
  border-color: #10B981;
  color: white;
}

body.dark-mode .custom-goal label {
  color: #D1D5DB;
}

body.dark-mode .custom-goal input {
  background: #374151;
  border-color: #4B5563;
  color: white;
}

body.dark-mode .custom-goal input:focus {
  border-color: #10B981;
}

body.dark-mode .apply-custom-btn {
  background: #10B981;
  color: white;
}

body.dark-mode .apply-custom-btn:hover:not(:disabled) {
  background: #059669;
}

body.dark-mode .apply-custom-btn:disabled {
  background: #4B5563;
}

body.dark-mode .toast {
  background: #1F2937;
  color: white;
  border-left-color: #10B981;
}
</style>