<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" @click="close"></div>

      <!-- Modal panel -->
      <div class="inline-block overflow-hidden text-right align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right w-full">
              <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                تنظیمات حساب کاربری
              </h3>
              
              <!-- Name Change Form -->
              <form @submit.prevent="updateName" class="mt-4">
                <div class="form-group">
                  <label for="name" class="form-label">نام و نام خانوادگی</label>
                  <div class="input-with-button">
                    <input 
                      type="text" 
                      id="name" 
                      v-model="nameForm.name" 
                      :disabled="nameForm.isSubmitting" 
                      placeholder="نام خود را وارد کنید"
                      class="form-input"
                    >
                    <button 
                      type="submit" 
                      :disabled="!nameForm.name || nameForm.isSubmitting"
                      class="btn btn-primary btn-sm"
                      :class="{ 'btn-disabled': !nameForm.name || nameForm.isSubmitting }"
                    >
                      <font-awesome-icon 
                        v-if="nameForm.isSubmitting" 
                        icon="fa-solid fa-circle-notch" 
                        spin 
                        class="ml-1"
                      />
                      <font-awesome-icon v-else icon="fa-solid fa-check" />
                    </button>
                  </div>
                  <div v-if="nameForm.message" :class="[nameForm.error ? 'form-error' : 'form-success']">
                    {{ nameForm.message }}
                  </div>
                </div>
              </form>

              <!-- Password Change Form -->
              <form @submit.prevent="updatePassword" class="mt-6 pb-3 border-t border-gray-200 pt-4">
                <h4 class="font-medium text-gray-900 mb-3">تغییر رمز عبور</h4>
                
                <div class="form-group">
                  <label for="current_password" class="form-label">رمز عبور فعلی</label>
                  <div class="password-input-wrapper">
                    <input 
                      :type="passwordVisibility.current ? 'text' : 'password'" 
                      id="current_password" 
                      v-model="passwordForm.current_password" 
                      :disabled="passwordForm.isSubmitting" 
                      placeholder="رمز عبور فعلی خود را وارد کنید"
                      class="form-input"
                    >
                    <button 
                      type="button" 
                      class="password-toggle" 
                      @click="togglePasswordVisibility('current')"
                    >
                      <font-awesome-icon :icon="passwordVisibility.current ? 'eye-slash' : 'eye'" />
                    </button>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="password" class="form-label">رمز عبور جدید</label>
                  <div class="password-input-wrapper">
                    <input 
                      :type="passwordVisibility.new ? 'text' : 'password'" 
                      id="password" 
                      v-model="passwordForm.password" 
                      :disabled="passwordForm.isSubmitting" 
                      placeholder="رمز عبور جدید را وارد کنید"
                      class="form-input"
                    >
                    <button 
                      type="button" 
                      class="password-toggle" 
                      @click="togglePasswordVisibility('new')"
                    >
                      <font-awesome-icon :icon="passwordVisibility.new ? 'eye-slash' : 'eye'" />
                    </button>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="password_confirmation" class="form-label">تکرار رمز عبور جدید</label>
                  <div class="password-input-wrapper">
                    <input 
                      :type="passwordVisibility.confirm ? 'text' : 'password'" 
                      id="password_confirmation" 
                      v-model="passwordForm.password_confirmation" 
                      :disabled="passwordForm.isSubmitting" 
                      placeholder="رمز عبور جدید را تکرار کنید"
                      class="form-input"
                    >
                    <button 
                      type="button" 
                      class="password-toggle" 
                      @click="togglePasswordVisibility('confirm')"
                    >
                      <font-awesome-icon :icon="passwordVisibility.confirm ? 'eye-slash' : 'eye'" />
                    </button>
                  </div>
                </div>
                
                <button 
                  type="submit" 
                  :disabled="!isPasswordFormValid || passwordForm.isSubmitting"
                  class="btn btn-primary w-full"
                  :class="{ 'btn-disabled': !isPasswordFormValid || passwordForm.isSubmitting }"
                >
                  <font-awesome-icon 
                    v-if="passwordForm.isSubmitting" 
                    icon="fa-solid fa-circle-notch" 
                    spin 
                    class="ml-2"
                  />
                  <span v-else>تغییر رمز عبور</span>
                </button>
                
                <div v-if="passwordForm.message" :class="[passwordForm.error ? 'form-error' : 'form-success']">
                  {{ passwordForm.message }}
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <!-- Modal footer -->
        <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
          <button 
            type="button" 
            class="btn btn-secondary w-full sm:w-auto px-6 py-2.5 text-base"
            @click="close"
          >
            بستن
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import axios from 'axios';
import { useStore } from 'vuex';

export default {
  name: 'ProfileSettingsModal',
  components: {
    FontAwesomeIcon
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

    // Check if password form is valid
    const isPasswordFormValid = computed(() => {
      return passwordForm.value.current_password && 
             passwordForm.value.password && 
             passwordForm.value.password_confirmation &&
             passwordForm.value.password === passwordForm.value.password_confirmation &&
             passwordForm.value.password.length >= 6;
    });

    // Update name
    const updateName = async () => {
      if (!nameForm.value.name) return;
      
      nameForm.value.isSubmitting = true;
      nameForm.value.message = '';
      nameForm.value.error = false;
      
      try {
        const token = localStorage.getItem('token');
        if (!token) throw new Error('No authentication token found');
        
        const response = await axios.put(
          `${import.meta.env.VITE_API_URL}/profile`, 
          { name: nameForm.value.name },
          {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            withCredentials: true
          }
        );
        
        nameForm.value.message = 'نام شما با موفقیت به‌روزرسانی شد';
        nameForm.value.error = false;
        
        // Emit event to update parent component
        emit('name-updated', nameForm.value.name);
        
        // Update user in store
        if (response.data?.profile) {
          store.commit('updateUser', response.data.profile);
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
    const updatePassword = async () => {
      if (!isPasswordFormValid.value) return;
      
      passwordForm.value.isSubmitting = true;
      passwordForm.value.message = '';
      passwordForm.value.error = false;
      
      try {
        const token = localStorage.getItem('token');
        if (!token) throw new Error('No authentication token found');
        
        const response = await axios.put(
          `${import.meta.env.VITE_API_URL}/profile`, 
          {
            password: passwordForm.value.password,
            current_password: passwordForm.value.current_password,
            password_confirmation: passwordForm.value.password_confirmation
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
      isPasswordFormValid,
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