<template>
  <div class="flex flex-col justify-center items-center min-h-screen bg-gray-50 p-4">
    <div class="w-full max-w-md">
      <h1 class="text-3xl font-bold text-gray-800 text-center mb-6">بازیابی رمز عبور</h1>

      <div class="bg-white rounded-xl shadow-md p-6 md:p-8">
        <Form @submit="requestReset" class="space-y-5" v-slot="{ errors, isSubmitting }">
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

          <button
            type="submit"
            class="w-full flex justify-center items-center py-3 px-4 rounded-lg text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200 font-medium text-base disabled:bg-gray-300 disabled:cursor-not-allowed"
            :disabled="Object.keys(errors).length > 0 || isProcessing || isSubmitting"
          >
            <span v-if="isProcessing" class="inline-block" style="margin-inline-end: 0.5rem;">
              <font-awesome-icon icon="fa-solid fa-spinner" spin class="text-base" />
            </span>
            ارسال لینک بازیابی
          </button>

          <div v-if="serverMessage" class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
            {{ serverMessage }}
          </div>
          <div v-if="serverError" class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm font-medium">
            {{ serverError }}
          </div>
        </Form>

        <div class="mt-6 text-center">
          <RouterLink to="/login" class="text-primary-600 hover:text-primary-700 font-medium">
            بازگشت به ورود
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import { Form, Field, ErrorMessage } from 'vee-validate';

const authAxios = axios.create({
  baseURL: BASE_API_URL,
  timeout: 10000,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
});

export default {
  name: 'ForgotPasswordView',
  components: { Form, Field, ErrorMessage },
  data() {
    return {
      email: '',
      emailRules: 'required|email',
      isProcessing: false,
      serverMessage: '',
      serverError: ''
    };
  },
  methods: {
    async requestReset() {
      if (this.isProcessing) return;
      this.serverError = '';
      this.serverMessage = '';
      try {
        this.isProcessing = true;
        const { data } = await authAxios.post('/auth/forgot-password', { email: this.email });
        this.serverMessage = data?.message || 'اگر ایمیل صحیح باشد، لینک بازیابی ارسال شد';
      } catch (err) {
        this.serverError = 'خطا در ارسال لینک بازیابی';
      } finally {
        this.isProcessing = false;
      }
    }
  }
}
</script>

<style scoped>
</style>


