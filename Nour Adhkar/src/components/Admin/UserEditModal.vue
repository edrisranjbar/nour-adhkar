<template>
  <div v-if="show" class="modal-overlay" @click="$emit('close')">
    <div class="edit-modal" @click.stop>
      <div class="modal-header">
        <h2>{{ user.id ? 'ویرایش کاربر' : 'کاربر جدید' }}</h2>
        <button class="close-button" @click="$emit('close')">
          <font-awesome-icon icon="fa-solid fa-times" />
        </button>
      </div>
      
      <div class="modal-body">
        <div class="form-group">
          <label for="user-name">نام کاربر</label>
          <input 
            type="text" 
            id="user-name" 
            v-model="editedUser.name" 
            class="form-input"
            :disabled="saving"
          />
        </div>
        
        <div class="form-group">
          <label for="user-email">ایمیل</label>
          <input 
            type="email" 
            id="user-email" 
            v-model="editedUser.email" 
            class="form-input"
            :disabled="saving"
          />
        </div>
        
        <div class="form-group">
          <label for="user-role">نقش کاربر</label>
          <select id="user-role" v-model="editedUser.role" class="form-input" :disabled="saving">
            <option value="user">کاربر عادی</option>
            <option value="admin">مدیر</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="user-status">وضعیت</label>
          <div class="toggle-switch">
            <input 
              type="checkbox" 
              id="user-status" 
              v-model="editedUser.active"
              :disabled="saving"
            />
            <label for="user-status" class="switch-label"></label>
            <span class="status-text">{{ editedUser.active ? 'فعال' : 'غیرفعال' }}</span>
          </div>
        </div>
        
        <div v-if="!user.id" class="form-group">
          <label for="user-password">کلمه عبور</label>
          <div class="password-input-wrapper">
            <input 
              :type="showPassword ? 'text' : 'password'" 
              id="user-password" 
              v-model="editedUser.password" 
              class="form-input"
              :disabled="saving"
            />
            <button 
              type="button" 
              class="password-toggle" 
              @click="showPassword = !showPassword"
              :disabled="saving"
            >
              <font-awesome-icon :icon="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" />
            </button>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button class="cancel-button" @click="$emit('close')" :disabled="saving">انصراف</button>
        <button class="save-button" @click="saveUser" :disabled="saving">
          <span v-if="saving" class="spinner"></span>
          <span v-else>ذخیره</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { library } from '@fortawesome/fontawesome-svg-core';
import { 
  faTimes, 
  faEye, 
  faEyeSlash 
} from '@fortawesome/free-solid-svg-icons';

// Add icons to the library
library.add(
  faTimes, 
  faEye, 
  faEyeSlash
);

export default {
  name: 'UserEditModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object,
      default: () => ({
        id: null,
        name: '',
        email: '',
        role: 'user',
        active: true,
        password: ''
      })
    },
    saving: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      editedUser: { ...this.user },
      showPassword: false
    };
  },
  watch: {
    user: {
      handler(newUser) {
        this.editedUser = { ...newUser };
      },
      deep: true,
      immediate: true
    },
    saving: {
      handler(newVal) {
        console.log('Saving state changed:', newVal);
      }
    }
  },
  methods: {
    saveUser() {
      console.log('Emitting save event with user:', this.editedUser);
      this.$emit('save', this.editedUser);
    }
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.edit-modal {
  background-color: white;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem;
  border-bottom: 1px solid #eee;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #333;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: #777;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #555;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-family: inherit;
  font-size: 0.95rem;
}

.form-input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.password-input-wrapper {
  position: relative;
}

.password-toggle {
  position: absolute;
  top: 50%;
  left: 10px;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #777;
  cursor: pointer;
}

.password-toggle:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.toggle-switch {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.toggle-switch input {
  display: none;
}

.switch-label {
  position: relative;
  display: inline-block;
  width: 46px;
  height: 24px;
  background-color: #eee;
  border-radius: 12px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.switch-label:after {
  content: '';
  position: absolute;
  top: 2px;
  right: 2px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background-color: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s;
}

input:checked + .switch-label {
  background-color: #A79277;
}

input:checked + .switch-label:after {
  transform: translateX(-22px);
}

input:disabled + .switch-label {
  opacity: 0.5;
  cursor: not-allowed;
}

.status-text {
  font-size: 0.9rem;
  color: #666;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.25rem;
  border-top: 1px solid #eee;
}

.cancel-button,
.save-button {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-family: inherit;
  font-size: 0.95rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.cancel-button {
  background-color: #f5f5f5;
  border: 1px solid #ddd;
  color: #666;
}

.cancel-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.save-button {
  background-color: #A79277;
  border: none;
  color: white;
  min-width: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.save-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.cancel-button:hover:not(:disabled) {
  background-color: #ebebeb;
}

.save-button:hover:not(:disabled) {
  background-color: #967f69;
}

.spinner {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  margin: 0;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Dark mode styles */
body.dark-mode .edit-modal {
  background-color: #333;
}

body.dark-mode .modal-header,
body.dark-mode .modal-footer {
  border-color: #444;
}

body.dark-mode .modal-header h2 {
  color: #eee;
}

body.dark-mode .close-button {
  color: #aaa;
}

body.dark-mode .form-group label {
  color: #bbb;
}

body.dark-mode .form-input {
  background-color: #333;
  border-color: #444;
  color: #eee;
}

body.dark-mode .form-input:disabled {
  background-color: #2a2a2a;
}

body.dark-mode .switch-label {
  background-color: #444;
}

body.dark-mode .switch-label:after {
  background-color: #666;
}

body.dark-mode .status-text {
  color: #aaa;
}

body.dark-mode .cancel-button {
  background-color: #444;
  border-color: #555;
  color: #ddd;
}

body.dark-mode .cancel-button:hover:not(:disabled) {
  background-color: #555;
}
</style> 