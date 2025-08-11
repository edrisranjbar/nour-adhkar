<template>
  <div class="flex flex-col justify-center items-center min-h-screen bg-gray-50 p-4">
    <div class="w-full max-w-md">
      <!-- Logo/Branding could go here -->
      <h1 class="text-3xl font-bold text-gray-800 text-center mb-6">ثبت نام</h1>
      
      <div class="bg-white rounded-xl shadow-md p-6 md:p-8">
        <Form @submit="register" class="space-y-5" v-slot="{ errors, isSubmitting }">
          <!-- Name Field -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">نام</label>
            <Field 
              id="name"
              name="name" 
              v-model="name" 
              type="text" 
              placeholder="نام کامل" 
              :rules="nameRules" 
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-gray-900" 
            />
            <ErrorMessage name="name" class="mt-1 text-sm text-red-600" />
          </div>
          
          <!-- Email Field -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">ایمیل</label>
            <Field 
              id="email"
              name="email" 
              v-model="email" 
              type="email" 
              placeholder="example@email.com" 
              :rules="emailRules" 
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-gray-900" 
            />
            <ErrorMessage name="email" class="mt-1 text-sm text-red-600" />
          </div>
          
          <!-- Password Field -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">رمز عبور</label>
            <div class="relative">
              <Field 
                id="password"
                name="password" 
                v-model="password" 
                :type="passwordVisible ? 'text' : 'password'" 
                placeholder="******" 
                :rules="passwordRules" 
                class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-gray-900" 
              />
              <button 
                type="button" 
                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                @click="togglePasswordVisibility" 
                tabindex="-1"
                aria-label="Toggle password visibility"
              >
                <font-awesome-icon :icon="passwordVisible ? 'eye-slash' : 'eye'" class="text-lg" />
              </button>
            </div>
            <ErrorMessage name="password" class="mt-1 text-sm text-red-600" />
          </div>
          
          <!-- Password Confirmation Field -->
          <div>
            <label for="passwordConfirm" class="block text-sm font-medium text-gray-700 mb-1">تکرار رمز عبور</label>
            <div class="relative">
              <Field 
                id="passwordConfirm"
                name="passwordConfirm" 
                v-model="passwordConfirm" 
                :type="passwordConfirmVisible ? 'text' : 'password'" 
                placeholder="******" 
                :rules="confirmPasswordRules" 
                class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-gray-900" 
              />
              <button 
                type="button" 
                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                @click="togglePasswordConfirmVisibility" 
                tabindex="-1"
                aria-label="Toggle password visibility"
              >
                <font-awesome-icon :icon="passwordConfirmVisible ? 'eye-slash' : 'eye'" class="text-lg" />
              </button>
            </div>
            <ErrorMessage name="passwordConfirm" class="mt-1 text-sm text-red-600" />
          </div>
          
          <!-- Submit Button -->
          <button 
            type="submit" 
            class="w-full flex justify-center items-center py-3 px-4 rounded-lg text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200 font-medium text-base disabled:bg-gray-300 disabled:cursor-not-allowed"
            :disabled="Object.keys(errors).length > 0 || isProcessing || isSubmitting"
          >
            <span v-if="isProcessing" class="inline-block" style="margin-inline-end: 0.5rem;">
              <font-awesome-icon icon="fa-solid fa-spinner" spin class="text-base" />
            </span>
            ثبت نام
          </button>
          
          <div 
            v-if="serverError" 
            class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm font-medium"
          >
            {{ serverError }}
          </div>
        </Form>
        
        <!-- Additional Links -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            قبلاً ثبت نام کرده‌اید؟ 
            <RouterLink to="/login" class="text-primary-600 hover:text-primary-700 font-medium">
              ورود به حساب کاربری
            </RouterLink>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import { Form, Field, ErrorMessage } from 'vee-validate';

// Create a clean axios instance for auth requests that won't trigger the token refresh interceptor
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
      name: '',
      email: '',
      password: '',
      passwordConfirm: '',
      serverError: '',
      nameRules: 'required|min:3',
      emailRules: 'required|email',
      passwordRules: 'required|min:8',
      isProcessing: false,
      passwordVisible: false,
      passwordConfirmVisible: false
    };
  },
  computed: {
    confirmPasswordRules() {
      return `required|confirmed:${this.password}`;
    }
  },
  methods: {
    togglePasswordVisibility() {
      this.passwordVisible = !this.passwordVisible;
    },
    
    togglePasswordConfirmVisibility() {
      this.passwordConfirmVisible = !this.passwordConfirmVisible;
    },
    
    async register() {
      // Return early if already processing
      if (this.isProcessing) {
        return;
      }
      
      try {
        this.serverError = '';
        this.isProcessing = true;
        
        // Use the clean authAxios instance that doesn't have the refresh interceptor
        const response = await authAxios.post('/auth/register', {
          name: this.name,
          email: this.email,
          password: this.password,
        });

        if (response.data.success) {
          // Update Vuex store
          this.$store.commit('setUser', response.data.user);
          this.$store.commit('setToken', response.data.token);
          
          // Set default authorization header for future requests
          axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
          
          // Store token in localStorage
          localStorage.setItem('token', response.data.token);
          
          // Redirect to home
          this.$router.push('/');
        }
      } catch (err) {
        console.error('Registration error:', err);
        
        if (err.response?.data?.errors) {
          // Format validation errors
          const errors = err.response.data.errors;
          const errorMessages = [];
          
          for (const field in errors) {
            if (Array.isArray(errors[field])) {
              // Translate specific error messages
              const errorMsg = errors[field][0];
              if (errorMsg === "The email has already been taken.") {
                errorMessages.push("این ایمیل قبلاً استفاده شده است.");
              } else {
                errorMessages.push(errorMsg);
              }
            }
          }
          
          this.serverError = errorMessages.join(' | ');
        } else if (err.code === 'ECONNABORTED') {
          this.serverError = 'زمان درخواست به پایان رسید. لطفاً دوباره تلاش کنید.';
        } else if (!navigator.onLine) {
          this.serverError = 'اتصال اینترنت خود را بررسی کنید';
        } else {
          this.serverError = err.response?.data?.message || 'ثبت نام با مشکل مواجه شد';
        }
      } finally {
        this.isProcessing = false;
      }
    },
  },
};
</script>