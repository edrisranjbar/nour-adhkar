<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay with blur effect -->
      <div class="fixed inset-0 transition-all duration-300 bg-black/50 backdrop-blur-sm" aria-hidden="true"
        @click="close"></div>

      <!-- Modal panel -->
      <div
        class="inline-block overflow-hidden text-right align-bottom transition-all duration-300 transform bg-white/90 backdrop-blur-md rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white/20">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4 rounded-t-3xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <font-awesome-icon icon="fa-solid fa-user-cog" class="text-white text-lg" />
              </div>
              <h3 class="text-xl font-bold text-white" id="modal-title">
                تنظیمات حساب کاربری
              </h3>
            </div>
            <button @click="close"
              class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-all duration-300 hover:scale-110">
              <font-awesome-icon icon="fa-solid fa-times" class="text-white text-sm" />
            </button>
          </div>
        </div>

        <!-- Modal Content -->
        <div class="px-6 py-6 bg-white/80 backdrop-blur-sm">
          <div class="w-full">

            <!-- Name Change Form -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100 mb-6">
              <div class="flex items-center gap-3 mb-4">
                <div
                  class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center">
                  <font-awesome-icon icon="fa-solid fa-user" class="text-white text-sm" />
                </div>
                <h4 class="text-lg font-bold text-gray-800">اطلاعات شخصی</h4>
              </div>

              <Form @submit="updateName" v-slot="{ errors }">
                <div class="form-group">
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-2">نام و نام خانوادگی</label>
                  <div class="flex gap-2">
                    <Field name="name" id="name" v-model="nameForm.name" :disabled="nameForm.isSubmitting"
                      placeholder="نام خود را وارد کنید" :rules="nameRules"
                      class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all duration-300 bg-white/80 backdrop-blur-sm" />
                    <button type="submit"
                      :disabled="Object.keys(errors).length > 0 || !nameForm.name || nameForm.isSubmitting"
                      class="px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-xl transition-all duration-300 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 flex items-center justify-center">
                      <font-awesome-icon v-if="nameForm.isSubmitting" icon="fa-solid fa-circle-notch" spin
                        class="text-sm" />
                      <font-awesome-icon v-else icon="fa-solid fa-check" class="text-sm" />
                    </button>
                  </div>
                  <ErrorMessage name="name" class="text-red-500 text-sm mt-2 text-right" />
                  <div v-if="nameForm.message"
                    :class="[nameForm.error ? 'text-red-500' : 'text-green-600', 'text-sm mt-2 text-right font-medium']">
                    {{ nameForm.message }}
                  </div>
                </div>
              </Form>
            </div>

            <!-- Password Change Form -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-100">
              <div class="flex items-center gap-3 mb-4">
                <div
                  class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                  <font-awesome-icon icon="fa-solid fa-lock" class="text-white text-sm" />
                </div>
                <h4 class="text-lg font-bold text-gray-800">تغییر رمز عبور</h4>
              </div>

              <Form @submit="updatePassword" v-slot="{ errors }">
                <div class="space-y-4">
                  <div class="form-group">
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">رمز عبور
                      فعلی</label>
                    <div class="relative">
                      <Field name="current_password" :type="passwordVisibility.current ? 'text' : 'password'"
                        id="current_password" v-model="passwordForm.current_password"
                        :disabled="passwordForm.isSubmitting" placeholder="رمز عبور فعلی خود را وارد کنید"
                        :rules="currentPasswordRules"
                        class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-300 bg-white/80 backdrop-blur-sm" />
                      <button type="button"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400 hover:text-gray-600 transition-colors"
                        @click="togglePasswordVisibility('current')">
                        <font-awesome-icon
                          :icon="passwordVisibility.current ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" />
                      </button>
                    </div>
                    <ErrorMessage name="current_password" class="text-red-500 text-sm mt-2 text-right" />
                  </div>

                  <div class="form-group">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">رمز عبور جدید</label>
                    <div class="relative">
                      <Field name="password" :type="passwordVisibility.new ? 'text' : 'password'" id="password"
                        v-model="passwordForm.password" :disabled="passwordForm.isSubmitting"
                        placeholder="رمز عبور جدید را وارد کنید" :rules="passwordRules"
                        class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-300 bg-white/80 backdrop-blur-sm" />
                      <button type="button"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400 hover:text-gray-600 transition-colors"
                        @click="togglePasswordVisibility('new')">
                        <font-awesome-icon
                          :icon="passwordVisibility.new ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" />
                      </button>
                    </div>
                    <ErrorMessage name="password" class="text-red-500 text-sm mt-2 text-right" />
                  </div>

                  <div class="form-group">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">تکرار رمز
                      عبور جدید</label>
                    <div class="relative">
                      <Field name="password_confirmation" :type="passwordVisibility.confirm ? 'text' : 'password'"
                        id="password_confirmation" v-model="passwordForm.password_confirmation"
                        :disabled="passwordForm.isSubmitting" placeholder="رمز عبور جدید را تکرار کنید"
                        :rules="passwordConfirmationRules"
                        class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-300 bg-white/80 backdrop-blur-sm" />
                      <button type="button"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400 hover:text-gray-600 transition-colors"
                        @click="togglePasswordVisibility('confirm')">
                        <font-awesome-icon
                          :icon="passwordVisibility.confirm ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" />
                      </button>
                    </div>
                    <ErrorMessage name="password_confirmation" class="text-red-500 text-sm mt-2 text-right" />
                  </div>

                  <button type="submit" :disabled="Object.keys(errors).length > 0 || passwordForm.isSubmitting"
                    class="w-full px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl font-medium transition-all duration-300 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 flex items-center justify-center gap-2">
                    <font-awesome-icon v-if="passwordForm.isSubmitting" icon="fa-solid fa-circle-notch" spin
                      class="text-sm" />
                    <font-awesome-icon v-else icon="fa-solid fa-key" class="text-sm" />
                    <span>تغییر رمز عبور</span>
                  </button>

                  <div v-if="passwordForm.message"
                    :class="[passwordForm.error ? 'text-red-500' : 'text-green-600', 'text-sm text-right font-medium']">
                    {{ passwordForm.message }}
                  </div>
                </div>
              </Form>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-slate-100 rounded-b-3xl border-t border-gray-200">
        <div class="flex justify-end">
          <button type="button"
            class="px-6 py-3 bg-gradient-to-r from-gray-500 to-slate-600 hover:from-gray-600 hover:to-slate-700 text-white rounded-xl font-medium transition-all duration-300 hover:scale-105 flex items-center gap-2"
            @click="close">
            <font-awesome-icon icon="fa-solid fa-times" class="text-sm" />
            بستن
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { Form, Field, ErrorMessage } from 'vee-validate';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faUserCog,
  faTimes,
  faUser,
  faLock,
  faEye,
  faEyeSlash,
  faCheck,
  faCircleNotch,
  faKey
} from '@fortawesome/free-solid-svg-icons';
import axios from 'axios';
import { useStore } from 'vuex';

// Add icons to the library
library.add(
  faUserCog,
  faTimes,
  faUser,
  faLock,
  faEye,
  faEyeSlash,
  faCheck,
  faCircleNotch,
  faKey
);

export default {
  name: 'ProfileSettingsModal',
  components: {
    FontAwesomeIcon,
    Form,
    Field,
    ErrorMessage
  },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    userName: {
      type: String,
      default: ''
    }
  },
  emits: ['close', 'name-updated'],
  setup(props, { emit }) {
    const store = useStore();

    // Name form state
    const nameForm = ref({
      name: props.userName || '',
      isSubmitting: false,
      message: '',
      error: false
    });

    // Password form state
    const passwordForm = ref({
      current_password: '',
      password: '',
      password_confirmation: '',
      isSubmitting: false,
      message: '',
      error: false
    });

    // Validation rules
    const nameRules = 'required|min:3';
    const currentPasswordRules = 'required|min:6';
    const passwordRules = 'required|min:6';

    // Password confirmation rule that depends on the password value
    const passwordConfirmationRules = computed(() => {
      return `required|confirmed:${passwordForm.value.password}`;
    });

    // Password visibility state
    const passwordVisibility = ref({
      current: false,
      new: false,
      confirm: false
    });

    // Toggle password visibility
    const togglePasswordVisibility = (field) => {
      passwordVisibility.value[field] = !passwordVisibility.value[field];
    };

    // Watch for name prop changes
    watch(() => props.userName, (newName) => {
      nameForm.value.name = newName || '';
    });

    // Update name
    const updateName = async (values) => {
      nameForm.value.isSubmitting = true;
      nameForm.value.message = '';
      nameForm.value.error = false;

      try {
        const token = localStorage.getItem('token');
        if (!token) throw new Error('No authentication token found');

        const response = await axios.patch(
          `${import.meta.env.VITE_API_URL}/user/name`,
          { name: values.name },
          {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            withCredentials: true
          }
        );

        nameForm.value.message = 'نام شما با موفقیت به‌روزرسانی شد';
        nameForm.value.error = false;

        // Update local form value
        nameForm.value.name = values.name;

        // Emit event to update parent component
        emit('name-updated', values.name);

        // Update user in store
        if (response.data?.user) {
          store.commit('updateUser', response.data.user);
        }
      } catch (error) {
        console.error('Error updating name:', error);
        nameForm.value.message = error.response?.data?.message || 'خطا در به‌روزرسانی نام';
        nameForm.value.error = true;
      } finally {
        nameForm.value.isSubmitting = false;
      }
    };

    // Update password
    const updatePassword = async (values) => {
      passwordForm.value.isSubmitting = true;
      passwordForm.value.message = '';
      passwordForm.value.error = false;

      try {
        const token = localStorage.getItem('token');
        if (!token) throw new Error('No authentication token found');

        const response = await axios.patch(
          `${import.meta.env.VITE_API_URL}/user/password`,
          {
            current_password: values.current_password,
            password: values.password,
            password_confirmation: values.password_confirmation
          },
          {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            withCredentials: true
          }
        );

        passwordForm.value.message = 'رمز عبور شما با موفقیت به‌روزرسانی شد';
        passwordForm.value.error = false;

        // Reset form
        passwordForm.value.current_password = '';
        passwordForm.value.password = '';
        passwordForm.value.password_confirmation = '';

        // Reset visibility state
        passwordVisibility.value.current = false;
        passwordVisibility.value.new = false;
        passwordVisibility.value.confirm = false;
      } catch (error) {
        console.error('Error updating password:', error);
        passwordForm.value.message = error.response?.data?.message || 'خطا در به‌روزرسانی رمز عبور';
        passwordForm.value.error = true;
      } finally {
        passwordForm.value.isSubmitting = false;
      }
    };

    // Close modal
    const close = () => {
      emit('close');
    };

    return {
      nameForm,
      passwordForm,
      passwordVisibility,
      nameRules,
      currentPasswordRules,
      passwordRules,
      passwordConfirmationRules,
      updateName,
      updatePassword,
      togglePasswordVisibility,
      close
    };
  }
}
</script>

<style scoped>
.password-input-wrapper {
  position: relative;
}

.password-toggle {
  position: absolute;
  top: 50%;
  left: 0.75rem;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #9CA3AF;
  cursor: pointer;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.password-toggle:hover {
  color: #6B7280;
}

.password-toggle:focus {
  outline: none;
}

.form-input {
  padding-left: 2.5rem;
}
</style>