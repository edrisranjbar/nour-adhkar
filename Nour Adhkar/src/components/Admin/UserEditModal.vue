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
  background-color: rgba(8, 11, 19, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.edit-modal {
  background-color: var(--admin-surface);
  border-radius: 14px;
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.6);
  border: 1px solid var(--admin-border);
  color: var(--admin-text);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--admin-border);
}

.modal-header h2 {
  margin: 0;
  font-size: 1.4rem;
  color: var(--admin-text);
}

.close-button {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: var(--admin-muted);
  cursor: pointer;
  transition: color 0.2s ease;
}

.close-button:hover {
  color: var(--admin-text);
}

.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: var(--admin-muted);
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--admin-border);
  border-radius: 10px;
  font-family: inherit;
  font-size: 0.95rem;
  background-color: rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: var(--admin-accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

.form-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.password-input-wrapper {
  position: relative;
}

.password-toggle {
  position: absolute;
  top: 50%;
  left: 12px;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--admin-muted);
  cursor: pointer;
}

.password-toggle:hover {
  color: var(--admin-accent);
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
  background-color: rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.switch-label:after {
  content: '';
  position: absolute;
  top: 2px;
  right: 2px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background-color: #fff;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.35);
  transition: transform 0.3s ease;
}

input:checked + .switch-label {
  background-color: rgba(74, 222, 128, 0.35);
}

input:checked + .switch-label:after {
  transform: translateX(-22px);
}

.status-text {
  font-size: 0.9rem;
  color: var(--admin-muted);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-top: 1px solid var(--admin-border);
}

.cancel-button,
.save-button {
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-family: inherit;
  font-size: 0.95rem;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.cancel-button {
  background-color: rgba(148, 163, 184, 0.2);
  border: none;
  color: var(--admin-muted);
}

.cancel-button:hover:not(:disabled) {
  background-color: rgba(148, 163, 184, 0.3);
  transform: translateY(-1px);
}

.save-button {
  background-color: var(--admin-accent);
  border: none;
  color: #fff;
  min-width: 90px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.save-button:hover:not(:disabled) {
  background-color: rgba(59, 130, 246, 0.85);
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
}

.save-button:disabled,
.cancel-button:disabled,
.password-toggle:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spinner {
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.35);
  border-top: 2px solid #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style> 