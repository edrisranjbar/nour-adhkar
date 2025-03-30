<template>
  <div class="login-container">
    <h1 class="login-title">وارد شوید</h1>
    <form @submit.prevent="requestLogin" class="login-form">
      <input v-model="email" type="email" placeholder="ایمیل" required class="login-input" />
      <input v-model="password" type="password" placeholder="رمز عبور" required class="login-input" />
      <button type="submit" class="login-button">ورود</button>
      <div v-if="error" class="error-message">{{ error }}</div>
    </form>
    <div class="footer">
      <p>حساب کاربری ندارید؟ <a href="/register">اینجا ثبت نام کنید</a></p>
    </div>
  </div>
</template>

<style scoped>
body {
  margin: 0;
  font-family: "Vazirmatn FD", sans-serif !important;
}
button,a,input {
  font-family: "Vazirmatn FD", sans-serif !important;
}
.login-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
  padding: 20px;
}

.login-title {
  margin-bottom: 20px;
  font-size: 2rem;
  color: #333;
}

.login-form {
  width: 100%;
  max-width: 400px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 20px;
}

.login-input {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
}

.login-input:focus {
  border-color: #007bff;
  outline: none;
}

.login-button {
  width: 100%;
  padding: 10px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.login-button:hover {
  background-color: #0056b3;
}

.error-message {
  color: red;
  margin-top: 10px;
  font-size: 0.875rem;
}

.footer {
  margin-top: 20px;
  text-align: center;
  font-size: 0.875rem;
}

.footer a {
  color: #007bff;
  text-decoration: none;
}

.footer a:hover {
  text-decoration: underline;
}
</style>

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