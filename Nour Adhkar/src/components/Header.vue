<template>
  <header>
    <h1>{{ title }}</h1>
    <p class="description">{{ description }}</p>
    <div class="button-container">
      <button v-if="!isLoggedIn" @click="goToLogin">ورود</button>
      <button v-else @click="goToDashboard">داشبورد</button>
    </div>
  </header>
</template>

<script>
import { mapState } from 'vuex';

export default {
  props: {
    title: String,
    description: String
  },
  name: 'Header',
  computed: {
    ...mapState(['user']), // Map user state from Vuex
    isLoggedIn() {
      return this.user !== null; // Determine if the user is logged in
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
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
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

</style>