<template>
  <div>
    <h1>Login</h1>
    <form @submit.prevent="requestLogin">
      <input v-model="email" type="email" placeholder="Email" required />
      <input v-model="password" type="password" placeholder="Password" required />
      <button type="submit">Login</button>
      <div v-if="error">{{ error }}</div>
    </form>
  </div>
</template>

<script>

import { mapActions } from 'vuex'; // Import mapActions to bind Vuex actions
import axios from 'axios';

export default {
  data() {
    return {
      email: '',
      password: '',
      error: '',
    };
  },
  methods: {
    ...mapActions(['login']), // Bind the login action from Vuex

    async requestLogin() {
      try {
        const response = await axios.post('http://localhost:8000/api/login', {
          email: this.email,
          password: this.password,
        });

        if (response.data.success) {
          // Directly commit mutations to update Vuex store
          this.$store.commit('setUser', response.data.user);
          this.$store.commit('setToken', response.data.token);
          this.$store.commit('updateHeartScore', response.data.heart_score);

          // Redirect to dashboard
          this.$router.push('/dashboard');
        } else {
          this.error = response.data.message || 'Login failed';
        }
      } catch (err) {
        this.error = err.response?.data?.error || 'An error occurred';
        console.error(err);
      }
    }
  },
};
</script>