<template>
  <div class="login-container">
    <h1 class="login-title">وارد شوید</h1>
    <Form @submit="requestLogin" class="login-form" v-slot="{ errors }">
      <div class="form-group">
        <Field name="email" v-model="email" type="email" placeholder="ایمیل" :rules="emailRules" class="login-input" />
        <ErrorMessage name="email" class="error-message" />
      </div>
      
      <div class="form-group">
        <Field name="password" v-model="password" type="password" placeholder="رمز عبور" :rules="passwordRules" class="login-input" />
        <ErrorMessage name="password" class="error-message" />
      </div>
      
      <button 
        type="submit" 
        class="login-button" 
        :disabled="Object.keys(errors).length > 0 || isProcessing"
      >
        <span v-if="isProcessing" class="loading-spinner">
          <font-awesome-icon icon="fa-solid fa-spinner" spin />
        </span>
        ورود
      </button>
      <div v-if="serverError" class="error-message">{{ serverError }}</div>
    </Form>
    <div class="footer">
      <p>حساب کاربری ندارید؟ <RouterLink to="/register">اینجا ثبت نام کنید</RouterLink></p>
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

.form-group {
  margin-bottom: 15px;
}

.login-input {
  width: 100%;
  padding: 10px;
  margin-bottom: 5px;
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
  background-color: #A79277;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.login-button:hover:not(:disabled) {
  background-color: #9C8466;
}

.login-button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.error-message {
  color: red;
  font-size: 0.875rem;
  margin-top: 4px;
}

.footer {
  margin-top: 20px;
  text-align: center;
  font-size: 0.875rem;
}

.footer a {
  color: #A79277;
  text-decoration: none;
}

.footer a:hover {
  text-decoration: underline;
}

.loading-spinner {
  font-size: 0.9rem;
}
</style>

<script>
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import { mapActions } from 'vuex';
import { Form, Field, ErrorMessage } from 'vee-validate';

export default {
  components: {
    Form,
    Field,
    ErrorMessage
  },
  data() {
    return {
      email: '',
      password: '',
      serverError: '',
      emailRules: 'required|email',
      passwordRules: 'required|min:6',
      isProcessing: false
    };
  },
  methods: {
    ...mapActions(['login']),

    async requestLogin() {
      try {
        this.serverError = '';
        this.isProcessing = true;
        const response = await axios.post(`${BASE_API_URL}/login`, {
          email: this.email,
          password: this.password,
        });

        if (response.data.token) {
          // Store user data and token in Vuex
          this.$store.commit('setUser', response.data.user);
          this.$store.commit('setToken', response.data.token);
          
          // Store token in localStorage for persistence
          localStorage.setItem('token', response.data.token);
          
          // Set default authorization header for future requests
          axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
          
          // Redirect to dashboard
          this.$router.push('/dashboard');
        } else {
          this.serverError = response.data.message || 'خطایی رخ داد';
        }
      } catch (err) {
        this.serverError = err.response?.data?.message || 'خطایی رخ داد';
        console.error(err);
      } finally {
        this.isProcessing = false;
      }
    }
  }
};
</script>