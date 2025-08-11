<template>
  <div class="settings-view">
    <header>
      <div class="d-flex">
        <RouterLink to="/" class="d-flex align-items-center">
          <img class="appbar-action-button" src="@/assets/icons/back-button.svg" alt="برگشتن">
        </RouterLink>
        <h1 id="modal-title">تنظیمات</h1>
      </div>
    </header>

    <main class="settings-container">
      <div class="settings-card">
        <h2>نمایش</h2>
        
        <div class="settings-item">
          <div class="setting-label">
            <div class="setting-title">حالت تاریک</div>
            <div class="setting-description">تغییر رنگ پس‌زمینه به حالت تاریک</div>
          </div>
          <label class="toggle">
            <input type="checkbox" v-model="settingsStore.darkMode" @change="settingsStore.saveSettings">
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="settings-item">
          <div class="setting-label">
            <div class="setting-title">اندازه متن</div>
            <div class="setting-description">تغییر اندازه متن قرآنی و ترجمه</div>
          </div>
          <div class="font-size-selector">
            <button @click="settingsStore.decreaseFontSize" :disabled="settingsStore.fontSize <= 1">
              <font-awesome-icon icon="fa-solid fa-minus" />
            </button>
            <span>{{ settingsStore.fontSize }}</span>
            <button @click="settingsStore.increaseFontSize" :disabled="settingsStore.fontSize >= 5">
              <font-awesome-icon icon="fa-solid fa-plus" />
            </button>
          </div>
        </div>
      </div>

      <div class="settings-card">
        <h2>اعلان‌ها</h2>
        
        <div class="settings-item">
          <div class="setting-label">
            <div class="setting-title">اعلان یادآوری</div>
            <div class="setting-description">دریافت یادآوری برای خواندن اذکار روزانه</div>
          </div>
          <label class="toggle">
            <input type="checkbox" v-model="settingsStore.notifications" @change="settingsStore.saveSettings">
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="settings-item">
          <div class="setting-label">
            <div class="setting-title">ارتعاش</div>
            <div class="setting-description">فعال‌سازی لرزش در هنگام شمارش اذکار</div>
          </div>
          <label class="toggle">
            <input type="checkbox" v-model="settingsStore.vibration" @change="settingsStore.saveSettings">
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="settings-item">
          <div class="setting-label">
            <div class="setting-title">صدا</div>
            <div class="setting-description">پخش صدا در هنگام شمارش اذکار</div>
          </div>
          <label class="toggle">
            <input type="checkbox" v-model="settingsStore.sound" @change="settingsStore.saveSettings">
            <span class="toggle-slider"></span>
          </label>
        </div>
      </div>

      <div class="settings-card">
        <h2>اطلاعات</h2>
        
        <div class="settings-item">
          <div class="setting-label">
            <div class="setting-title">نسخه برنامه</div>
            <div class="setting-description">1.0.0</div>
          </div>
        </div>

        <div v-if="isAuthenticated" class="settings-item clickable" @click="logoutUser">
          <div class="setting-label">
            <div class="setting-title danger">خروج از حساب کاربری</div>
          </div>
          <font-awesome-icon icon="fa-solid fa-sign-out-alt" />
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useSettingsStore } from '@/stores/settings';

export default {
  name: 'SettingsView',
  components: {
    FontAwesomeIcon
  },
  computed: {
    ...mapGetters(['isAuthenticated']),
    settingsStore() {
      return useSettingsStore();
    }
  },
  methods: {
    ...mapActions(['logoutUser']),
    async handleLogout() {
      if (confirm('آیا مطمئن هستید که می‌خواهید از حساب کاربری خود خارج شوید؟')) {
        await this.logoutUser();
        this.$router.push('/');
      }
    }
  }
};
</script>

<style scoped>
.settings-view {
  min-height: 100vh;
  max-width: 1000px;
  margin: auto;
  background: #D1BB9E;
  padding-top: 20px;
}

body.dark-mode .settings-view {
  background: #262626;
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

.settings-container {
  padding: 20px 0;
  margin: 0 auto;
}

.settings-card {
  background: rgba(240, 240, 240, .67);
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: rgba(0, 0, 0, .25) 0 4px 4px;
}

body.dark-mode .settings-card {
  background: rgba(50, 50, 50, 0.8);
  box-shadow: rgba(0, 0, 0, .4) 0 4px 8px;
}

.settings-card h2 {
  color: #9C8466;
  font-size: 1.3rem;
  margin-top: 0;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid rgba(156, 132, 102, 0.3);
}

body.dark-mode .settings-card h2 {
  color: #C5B192;
  border-bottom: 1px solid rgba(197, 177, 146, 0.3);
}

.settings-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding: 10px;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.settings-item.clickable {
  cursor: pointer;
  text-decoration: none;
}

.settings-item.clickable:hover {
  background: rgba(255, 255, 255, 0.5);
}

body.dark-mode .settings-item.clickable:hover {
  background: rgba(255, 255, 255, 0.1);
}

.setting-label {
  flex: 1;
}

.setting-title {
  font-weight: 500;
  color: #2C2A2A;
  margin-bottom: 4px;
}

body.dark-mode .setting-title {
  color: #EFEFEF;
}

.setting-title.danger {
  color: #e53935;
}

.setting-description {
  font-size: 0.9rem;
  color: #666;
}

body.dark-mode .setting-description {
  color: #AAAAAA;
}

/* Toggle Switch */
.toggle {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}

.toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked + .toggle-slider {
  background-color: #9C8466;
}

input:checked + .toggle-slider:before {
  transform: translateX(26px);
}

/* Font size selector */
.font-size-selector {
  display: flex;
  align-items: center;
  gap: 10px;
}

.font-size-selector button {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-color: #9C8466;
  color: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.font-size-selector button:hover:not(:disabled) {
  background-color: #7a6b4c;
}

body.dark-mode .font-size-selector button:hover:not(:disabled) {
  background-color: #C5B192;
}

.font-size-selector button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

body.dark-mode .font-size-selector button:disabled {
  background-color: #444;
}

.font-size-selector span {
  font-weight: 500;
  min-width: 20px;
  text-align: center;
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

/* Responsive */
@media (max-width: 480px) {
  .settings-view { padding-top: 0; }
  .settings-container {
    padding: 15px;
  }
  
  .settings-card {
    padding: 15px;
  }
  
  .settings-item {
    padding: 8px;
  }
}
</style>