<script>
import { RouterLink, RouterView } from 'vue-router'
import { useSettingsStore } from './stores/settings'
import { mapGetters, mapActions } from 'vuex'

export default {
  components: {
    RouterView
  },
  data() {
    return {
      showSplash: false,
      settingsStore: null,
      deferredPrompt: null,
      canInstall: false
    }
  },
  computed: {
    ...mapGetters(['isAuthenticated']),
    isAdminRoute() {
      // Check if the current route path starts with /admin
      return this.$route.path.startsWith('/admin')
    }
  },
  methods: {
    ...mapActions(['refreshUserData']),
    triggerInstall() {
      if (!this.deferredPrompt) return;
      this.deferredPrompt.prompt();
      this.deferredPrompt.userChoice.finally(() => {
        this.deferredPrompt = null;
        this.canInstall = false;
      });
    },
    checkSplashScreen() {
      // Get from local storage to check if the splash is currently showing
      const hasSplashBeenShown = localStorage.getItem('splashShown')
      this.showSplash = !hasSplashBeenShown
    },
    handleSplashScreenHidden() {
      this.showSplash = false
    },
    updateMetaTags() {
      // Update meta tags when route changes
      if (this.$route.meta && (this.$route.meta.title || this.$route.meta.description)) {
        this.$setMeta({
          title: this.$route.meta.title,
          description: this.$route.meta.description,
          url: `https://nour-adhkar.ir${this.$route.path}`
        });
      }
    }
  },
  watch: {
    $route(to) {
      // Update meta tags when route changes
      if (to.meta && (to.meta.title || to.meta.description)) {
        this.$setMeta({
          title: to.meta.title,
          description: to.meta.description,
          url: `https://nour-adhkar.ir${to.path}`
        });
      }
    }
  },
  created() {
    // Refresh user data if authenticated
    if (this.isAuthenticated) {
      console.log('App created - refreshing user data');
      this.refreshUserData();
    }
  },
  mounted() {
    // Set initial meta tags
    this.updateMetaTags();
    
    // Check splash screen visibility
    this.checkSplashScreen();
    
    // Listen for splash screen events
    window.addEventListener('splashScreenHidden', this.handleSplashScreenHidden);

    // Handle PWA install prompt
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      this.deferredPrompt = e;
      this.canInstall = true;
    });
    window.addEventListener('appinstalled', () => {
      this.deferredPrompt = null;
      this.canInstall = false;
    });
  },
  beforeUnmount() {
    window.removeEventListener('splashScreenHidden', this.handleSplashScreenHidden);
  }
}
</script>

<template>
  <div class="app-container">
    <RouterView />
    
    <!-- Bottom Navigation Bar - hidden when splash screen is shown or on admin routes -->
    <div class="bottom-navigation" v-if="!showSplash && !isAdminRoute">
      <div class="nav-container">
        <RouterLink to="/" class="nav-item" active-class="active">
          <font-awesome-icon icon="fa-solid fa-home" />
          <span>خانه</span>
        </RouterLink>
        <RouterLink to="/blog" class="nav-item" active-class="active">
          <font-awesome-icon icon="fa-solid fa-newspaper" />
          <span>مقالات</span>
        </RouterLink>
        <RouterLink to="/counter" class="nav-item" active-class="active">
          <font-awesome-icon icon="fa-solid fa-hands-praying" />
          <span>تسبیح</span>
        </RouterLink>
        <RouterLink v-if="isAuthenticated" to="/dashboard" class="nav-item" active-class="active">
          <font-awesome-icon icon="fa-solid fa-user" />
          <span>داشبورد</span>
        </RouterLink>
        <RouterLink to="/settings" class="nav-item" active-class="active">
          <font-awesome-icon icon="fa-solid fa-gear" />
          <span>تنظیمات</span>
        </RouterLink>
      </div>
    </div>

    <!-- Install PWA floating button -->
    <button v-if="canInstall && !isAdminRoute" class="pwa-install-btn" @click="triggerInstall" aria-label="نصب برنامه">
      <font-awesome-icon icon="fa-solid fa-download" />
      <span>نصب برنامه</span>
    </button>
  </div>
</template>

<style>
@import './assets/css/dark-mode.css';

:root {
  --font-size-factor: 1;
}

/* Global styles */
body {
  padding-bottom: 100px;
}

/* Remove bottom padding for admin pages */
body.admin-page {
  padding-bottom: 0 !important;
}

@media (max-width: 767px) {
  body {
    padding-bottom: 70px;
  }
  
  /* Even for mobile, admin pages should have no bottom padding */
  body.admin-page {
    padding-bottom: 0 !important;
  }
}

.app-container {
  position: relative;
  min-height: 100vh;
}
/* PWA install button */
.pwa-install-btn {
  position: fixed;
  right: 16px;
  bottom: 96px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #0ea5e9;
  color: #fff;
  border: none;
  border-radius: 9999px;
  padding: 10px 14px;
  box-shadow: 0 8px 24px rgba(14,165,233,0.4);
  cursor: pointer;
  z-index: 1100;
}

.pwa-install-btn:hover { filter: brightness(1.05); }

@media (max-width: 767px) {
  .pwa-install-btn { bottom: 86px; }
}

/* Bottom Navigation */
.bottom-navigation {
  position: fixed;
  bottom: 16px;
  left: 50%;
  transform: translateX(-50%);
  height: 70px;
  width: 92%;
  max-width: 500px;
  background-color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  border-radius: 16px;
}

/* Mobile view adjustments */
@media (max-width: 767px) {
  .bottom-navigation {
    bottom: 0;
    left: 0;
    transform: none;
    width: 100%;
    max-width: 100%;
    border-radius: 0;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
  }
  
  body.dark-mode .bottom-navigation {
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.3);
  }
}

.nav-container {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 10px;
}

body.dark-mode .bottom-navigation {
  background-color: #262626;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #777;
  text-decoration: none;
  padding: 8px 0;
  width: 20%;
  transition: all 0.25s ease;
}

body.dark-mode .nav-item {
  color: #888;
}

.nav-item span {
  font-size: 0.85rem;
  margin-top: 6px;
  transition: all 0.25s ease;
}

.nav-item svg {
  font-size: 1.5rem;
  transition: all 0.25s ease;
}

.nav-item:hover, .nav-item.active {
  color: #A79277;
}

body.dark-mode .nav-item:hover, 
body.dark-mode .nav-item.active {
  color: #C5B192;
}

.nav-item.active svg {
  transform: translateY(-3px);
}

.nav-item.active span {
  font-weight: 600;
}

@media (min-width: 768px) {
  .nav-item span {
    font-size: 0.9rem;
  }

  .nav-item svg {
    font-size: 1.7rem;
  }
}

@media (max-width: 350px) {
  .nav-item span {
    font-size: 0.75rem;
  }
  
  .nav-item svg {
    font-size: 1.2rem;
  }
}
</style>
