<template>
  <header>
    <h1>{{ title }}</h1>
    <p class="description">{{ description }}</p>
    <div class="button-container">
      <div v-if="isAuthenticated" class="user-stats">
        <button class="dashboard-button" @click="goToDashboard">
          <font-awesome-icon icon="fa-solid fa-user" />
          داشبورد
        </button>
        <div class="stat-item">
          <font-awesome-icon icon="fa-solid fa-star" class="stat-icon" />
          <span>{{ userScore }}</span>
        </div>
        <div class="stat-item">
          <font-awesome-icon icon="fa-solid fa-heart" class="stat-icon" />
          <span>{{ heartScore }}</span>
        </div>
        <div class="stat-item">
          <font-awesome-icon icon="fa-solid fa-fire" class="stat-icon" />
          <span>{{ streak }}</span>
        </div>
      </div>
      <button v-else @click="goToLogin">ورود</button>
    </div>
  </header>
</template>

<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { mapState, mapGetters } from 'vuex'

export default {
  components: {
    FontAwesomeIcon
  },
  props: {
    title: String,
    description: String
  },
  name: 'Header',
  computed: {
    ...mapState(['user']),
    ...mapGetters(['isAuthenticated']), // Use isAuthenticated instead of isLoggedIn
    userScore() {
      return this.user?.score || 0
    },
    heartScore() {
      return this.user?.heartScore || 0
    },
    streak() {
      return this.user?.streak || 0
    }
  },
  methods: {
    goToLogin() {
      this.$router.push('/login'); // Navigate to the login page
    },
    goToDashboard() {
      this.$router.push('/dashboard'); // Navigate to the dashboard
    }
  }
}
</script>

<style scoped>
.button-container {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: auto;
  gap: 16px;
}

button {
  padding: 10px 15px;
  margin-right: 10px;
  cursor: pointer;
  background-color: #9C8466;
  /* Your background color */
  outline: unset;
  border: unset;
  font-family: "Vazirmatn FD", sans-serif;
  /* Your custom font */
  border-radius: 8px;
  /* Rounded corners */
  display: flex;
  /* Flexbox for alignment */
  align-items: center;
  /* Center items vertically */
  font-size: 16px;
  /* Font size */
  transition: background-color 0.3s ease;
  /* Transition for hover effect */
}

button:hover {
  background-color: #7a6b4c;
  color: #fff;
}

.user-stats {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-right: 16px;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(240, 240, 240, .67);
  padding: 6px 12px;
  border-radius: 8px;
  box-shadow: rgba(0, 0, 0, .25) 0 2px 4px;
}

.stat-item span {
  color: #2C2A2A;
  font-weight: 500;
  font-size: 16px;
}

.stat-icon {
  color: #9C8466;
  font-size: 16px;
}

/* Hover effect */
.stat-item:hover {
  transform: translateY(-2px);
  box-shadow: rgba(149, 157, 165, 0.5) 0 4px 8px;
  transition: all 0.3s ease;
}

/* Responsive adjustments */
@media (max-width: 480px) {
  .user-stats {
    gap: 8px;
    margin-right: 8px;
  }
  
  .stat-item {
    padding: 4px 8px;
  }
  
  .stat-item span {
    font-size: 14px;
  }
  
  .stat-icon {
    font-size: 14px;
  }

  .button-container {
    gap: 8px;
  }
}

.dashboard-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #9C8466;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.dashboard-button:hover {
  background: #A79277;
  transform: translateY(-2px);
  box-shadow: rgba(149, 157, 165, 0.5) 0 8px 24px;
}

.dashboard-button:active {
  transform: translateY(0);
}
</style>