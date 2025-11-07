<template>
  <div class="modal-overlay" v-if="show" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3>ایجاد کاربر جدید</h3>
        <button class="close-button" @click="closeModal">&times;</button>
      </div>
      
      <div class="modal-body">
        <form @submit.prevent="saveUser">
          <div class="form-group">
            <label for="name">نام کاربر</label>
            <input 
              type="text" 
              id="name" 
              v-model="user.name" 
              placeholder="نام کاربر را وارد کنید" 
              class="form-control"
              required
            >
          </div>
          
          <div class="form-group">
            <label for="email">ایمیل</label>
            <input 
              type="email" 
              id="email" 
              v-model="user.email" 
              placeholder="ایمیل کاربر را وارد کنید" 
              class="form-control"
              required
            >
          </div>
          
          <div class="form-group">
            <label for="password">رمز عبور</label>
            <input 
              type="password" 
              id="password" 
              v-model="user.password" 
              placeholder="رمز عبور را وارد کنید" 
              class="form-control"
              required
            >
          </div>
          
          <div class="form-group">
            <label for="role">نقش کاربر</label>
            <select id="role" v-model="user.role" class="form-control">
              <option value="user">کاربر عادی</option>
              <option value="admin">مدیر</option>
            </select>
          </div>
          
          <div class="form-group">
            <label class="checkbox-label">
              <input 
                type="checkbox" 
                v-model="user.active"
              >
              <span>فعال</span>
            </label>
          </div>
          
          <div class="form-actions">
            <button type="button" class="cancel-button" @click="closeModal" :disabled="saving">انصراف</button>
            <button type="submit" class="save-button" :disabled="saving">
              <span v-if="saving">در حال ذخیره...</span>
              <span v-else>ذخیره</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserCreateModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    saving: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      user: {
        name: '',
        email: '',
        password: '',
        role: 'user',
        active: true
      }
    };
  },
  methods: {
    closeModal() {
      this.$emit('close');
    },
    saveUser() {
      this.$emit('save', { ...this.user });
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
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-content {
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

.modal-header h3 {
  margin: 0;
  font-size: 1.4rem;
  color: var(--admin-text);
}

.close-button {
  background: none;
  border: none;
  font-size: 1.4rem;
  cursor: pointer;
  color: var(--admin-muted);
  transition: color 0.2s ease;
}

.close-button:hover {
  color: var(--admin-text);
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: var(--admin-muted);
}

.form-control {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--admin-border);
  border-radius: 10px;
  font-size: 0.95rem;
  font-family: inherit;
  background-color: rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:focus {
  outline: none;
  border-color: var(--admin-accent);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

.checkbox-label {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  color: var(--admin-text);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.75rem;
}

.save-button,
.cancel-button {
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  border: none;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.save-button {
  background-color: var(--admin-accent);
  color: #fff;
}

.save-button:hover:not(:disabled) {
  background-color: rgba(59, 130, 246, 0.85);
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
}

.cancel-button {
  background-color: rgba(148, 163, 184, 0.2);
  color: var(--admin-muted);
}

.cancel-button:hover:not(:disabled) {
  background-color: rgba(148, 163, 184, 0.3);
  transform: translateY(-1px);
}

.save-button:disabled,
.cancel-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style> 