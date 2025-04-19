<template>
  <header class="app-header">
    <div class="header-container">
      <div class="header-left">
        <img src="@/assets/icons/logo.png" alt="اذکار نور" class="app-logo" />
        <div class="header-titles">
          <h1>{{ title }}</h1>
          <p class="description">{{ description }}</p>
        </div>
      </div>
      
      <div class="header-right">
        <div v-if="isAuthenticated" class="user-stats">
          <div class="stat-item">
            <font-awesome-icon icon="fa-solid fa-heart" class="stat-icon" />
            <span>{{ user?.heart_score || 0 }}</span>
          </div>
          <div class="stat-item">
            <font-awesome-icon icon="fa-solid fa-fire" class="stat-icon" />
            <span>{{ user?.streak || 0 }}</span>
          </div>
          <button @click="logout" class="logout-button">
            <font-awesome-icon icon="fa-solid fa-sign-out-alt" />
            خروج
          </button>
        </div>
        <button v-if="!isAuthenticated" @click="goToLogin" class="login-button">
          <font-awesome-icon icon="fa-solid fa-sign-in-alt" />
          ورود
        </button>
      </div>
    </div>
  </header>
</template>

<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { mapGetters, mapActions } from 'vuex'

export default {
  components: {
    FontAwesomeIcon
  },
  props: {
    title: String,
    description: String
  },
  name: 'AppHeader',
  computed: {
    ...mapGetters(['isAuthenticated', 'user'])
  },
  methods: {
    ...mapActions(['logoutUser']),
    goToLogin() {
      this.$router.push('/login');
    },
    async logout() {
      await this.logoutUser();
      this.$router.push('/');
    }
  }
}
</script>

<style scoped>
.app-header {
  padding: 16px 0;
  background-color: #A79277;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

body.dark-mode .app-header {
  background-color: #262626;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.header-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.app-logo {
  width: 50px;
  height: 50px;
  object-fit: contain;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  padding: 4px;
  background-color: #fff;
  transition: transform 0.3s ease;
}

body.dark-mode .app-logo {
  background-color: #333;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.app-logo:hover {
  transform: translateY(-3px) rotate(5deg);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.header-titles {
  display: flex;
  flex-direction: column;
}

h1 {
  font-size: 1.5rem;
  margin: 0;
  color: #fff;
  font-weight: 600;
}

body.dark-mode h1 {
  color: #fff;
}

.description {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.85);
  margin: 4px 0 0 0;
}

body.dark-mode .description {
  color: rgba(255, 255, 255, 0.7);
}

.header-right {
  display: flex;
  align-items: center;
}

.button-container {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: auto;
  gap: 16px;
}

button {
  padding: 10px 15px;
  cursor: pointer;
  background-color: #8A7559;
  outline: unset;
  border: unset;
  font-family: "Vazirmatn FD", sans-serif;
  border-radius: 8px;
  display: flex;
  align-items: center;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  color: white;
}

button:hover {
  background-color: #76644A;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.user-stats {
  display: flex;
  align-items: center;
  gap: 12px;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #8A7559;
  padding: 6px 12px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

body.dark-mode .stat-item {
  background: #333;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}

.stat-item span {
  color: #fff;
  font-weight: 600;
  font-size: 0.9rem;
}

body.dark-mode .stat-item span {
  color: #fff;
}

.stat-icon {
  color: #fff;
  font-size: 1rem;
}

/* Hover effect */
.stat-item:hover {
  transform: translateY(-2px);
  background: #76644A;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

body.dark-mode .stat-item:hover {
  background: #444;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
}

.dashboard-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  background: #8A7559;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.dashboard-button:hover {
  background: #76644A;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.login-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  background: #8A7559;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.login-button:hover {
  background: #76644A;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.logout-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  background: #76644A;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.logout-button:hover {
  background: #8A7559;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .header-container {
    flex-direction: column;
    align-items: center;
    gap: 16px;
    text-align: center;
  }
  
  .header-left {
    flex-direction: column;
    align-items: center;
  }
  
  .app-logo {
    width: 70px;
    height: 70px;
    margin-bottom: 10px;
  }
  
  .header-right {
    width: 100%;
    justify-content: center;
  }
  
  .user-stats {
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .description {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .user-stats {
    gap: 8px;
  }
  
  .stat-item {
    padding: 4px 8px;
  }
  
  .stat-item span {
    font-size: 0.8rem;
  }
  
  .stat-icon {
    font-size: 0.8rem;
  }
  
  .dashboard-button, .login-button {
    padding: 6px 10px;
    font-size: 0.8rem;
  }
  
  .app-logo {
    width: 65px;
    height: 65px;
  }
  
  h1 {
    font-size: 1.3rem;
  }
  
  .description {
    font-size: 1.3rem;
    line-height: 1.5;
  }
}
</style>