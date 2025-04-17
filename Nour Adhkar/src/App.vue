<script>
import { RouterLink, RouterView } from 'vue-router'
import { useSettingsStore } from './stores/settings'

export default {
  components: {
    RouterView
  },
  data() {
    return {
      showSplash: false,
      settingsStore: null
    }
  },
  computed: {
    isAdminRoute() {
      // Check if the current route path starts with /admin
      return this.$route.path.startsWith('/admin')
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
    // Initialize settings from the Pinia store
    this.settingsStore = useSettingsStore()
  },
  mounted() {
    // Initialize settings
    if (this.settingsStore) {
      this.settingsStore.init()
    }

    // Set initial meta tags
    if (this.$route.meta && (this.$route.meta.title || this.$route.meta.description)) {
      this.$setMeta({
        title: this.$route.meta.title,
        description: this.$route.meta.description,
        url: `https://nour-adhkar.ir${this.$route.path}`
      });
    }

    // Check if splash screen should be shown
    this.checkSplashScreen()

    // Listen for splash screen events
    window.addEventListener('splash-shown', () => {
      this.showSplash = true
    })
    window.addEventListener('splash-hidden', () => {
      this.showSplash = false
    })
  },
  beforeUnmount() {
    window.removeEventListener('splash-shown', () => {
      this.showSplash = true
    })
    window.removeEventListener('splash-hidden', () => {
      this.showSplash = false
    })
  },
  methods: {
    checkSplashScreen() {
      // Get from local storage to check if the splash is currently showing
      const hasSplashBeenShown = localStorage.getItem('splashShown')
      this.showSplash = !hasSplashBeenShown
    }
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
          <font-awesome-icon icon="fa-solid fa-calculator" />
          <span>تسبیح</span>
        </RouterLink>
        <RouterLink to="/dashboard" class="nav-item" active-class="active">
          <font-awesome-icon icon="fa-solid fa-user" />
          <span>پروفایل</span>
        </RouterLink>
        <RouterLink to="/settings" class="nav-item" active-class="active">
          <font-awesome-icon icon="fa-solid fa-gear" />
          <span>تنظیمات</span>
        </RouterLink>
      </div>
    </div>
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
  justify-content: space-around;
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
  width: 16.66%;
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
</style>
