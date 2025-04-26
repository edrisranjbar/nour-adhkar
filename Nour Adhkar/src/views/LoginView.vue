<template>
  <div class="login-container">
    <h1 class="login-title">وارد شوید</h1>
    <Form @submit="requestLogin" class="login-form" v-slot="{ errors, isSubmitting }">
      <div class="form-group">
        <Field name="email" v-model="email" type="email" placeholder="ایمیل" :rules="emailRules" class="login-input" />
        <ErrorMessage name="email" class="error-message" />
      </div>
      
      <div class="form-group">
        <div class="password-field">
          <Field 
            name="password" 
            v-model="password" 
            :type="passwordVisible ? 'text' : 'password'" 
            placeholder="رمز عبور" 
            :rules="passwordRules" 
            class="login-input password-input" 
          />
          <button 
            type="button" 
            class="password-toggle" 
            @click="togglePasswordVisibility" 
            tabindex="-1"
          >
            <font-awesome-icon :icon="passwordVisible ? 'eye-slash' : 'eye'" />
          </button>
        </div>
        <ErrorMessage name="password" class="error-message" />
      </div>
      
      <button 
        type="submit" 
        class="login-button" 
        :disabled="Object.keys(errors).length > 0 || isProcessing || isSubmitting"
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

.password-field {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input {
  padding-left: 40px; /* Make space for the eye icon */
}

.password-toggle {
  position: absolute;
  left: 10px;
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 0;
  font-size: 1rem;
}

.password-toggle:hover {
  color: #333;
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

// Create a clean axios instance for auth requests that won't trigger the interceptor
const authAxios = axios.create({
  baseURL: BASE_API_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

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
      isProcessing: false,
      passwordVisible: false
    };
  },
  methods: {
    ...mapActions(['login']),

    togglePasswordVisibility() {
      this.passwordVisible = !this.passwordVisible;
    },

    async requestLogin() {
      // Return early if already processing to prevent duplicate requests
      if (this.isProcessing) {
        return;
      }
      
      try {
        this.serverError = '';
        this.isProcessing = true;
        
        // Use the clean authAxios instance that doesn't have the refresh interceptor
        const response = await authAxios.post('/auth/login', {
          email: this.email,
          password: this.password,
        });

        if (response.data?.token) {
          // Store user data and token in Vuex
          this.$store.commit('setUser', response.data.user);
          this.$store.commit('setToken', response.data.token);
          
          // Store token in localStorage for persistence
          localStorage.setItem('token', response.data.token);
          
          // Set default authorization header for future requests
          axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
          
          // Redirect to home
          this.$router.push('/');
        } else {
          this.serverError = response.data?.message || 'خطای نامشخص در ورود';
        }
      } catch (err) {
        console.error('Login error:', err);
        
        if (err.code === 'ECONNABORTED') {
          this.serverError = 'زمان درخواست به پایان رسید. لطفاً دوباره تلاش کنید.';
        } else if (err.response?.status === 401) {
          // Handle 401 error for login specifically - clear password
          this.password = '';
          this.serverError = 'ایمیل یا رمز عبور اشتباه است';
        } else if (err.response?.data?.message) {
          this.serverError = err.response.data.message;
        } else if (!navigator.onLine) {
          this.serverError = 'اتصال اینترنت خود را بررسی کنید';
        } else {
          this.serverError = 'خطا در ورود به سیستم';
        }
      } finally {
        this.isProcessing = false;
      }
    }
  }
};
</script>