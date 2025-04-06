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
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
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
  padding: 16px 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: #333;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  padding: 0;
  line-height: 1;
}

.close-button:hover {
  color: #333;
}

.modal-body {
  padding: 20px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #333;
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 1rem;
  color: #495057;
}

.form-control:focus {
  border-color: #A79277;
  outline: 0;
  box-shadow: 0 0 0 2px rgba(167, 146, 119, 0.25);
}

.checkbox-label {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.checkbox-label input {
  margin-left: 8px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 24px;
}

.save-button,
.cancel-button {
  padding: 8px 16px;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  font-family: inherit;
}

.save-button {
  background-color: #A79277;
  color: white;
  border: none;
}

.save-button:hover {
  background-color: #8a7660;
}

.cancel-button {
  background-color: #f8f9fa;
  color: #495057;
  border: 1px solid #ced4da;
}

.cancel-button:hover {
  background-color: #e9ecef;
}

.save-button:disabled,
.cancel-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Dark mode styles */
body.dark-mode .modal-content {
  background-color: #333;
}

body.dark-mode .modal-header {
  border-bottom-color: #444;
}

body.dark-mode .modal-header h3 {
  color: #eee;
}

body.dark-mode .close-button {
  color: #aaa;
}

body.dark-mode .close-button:hover {
  color: #eee;
}

body.dark-mode .form-group label {
  color: #eee;
}

body.dark-mode .form-control {
  background-color: #444;
  border-color: #555;
  color: #eee;
}

body.dark-mode .form-control:focus {
  border-color: #C5B192;
  box-shadow: 0 0 0 2px rgba(197, 177, 146, 0.25);
}

body.dark-mode .save-button {
  background-color: #C5B192;
  color: #333;
}

body.dark-mode .save-button:hover {
  background-color: #A79277;
}

body.dark-mode .cancel-button {
  background-color: #444;
  color: #eee;
  border-color: #555;
}

body.dark-mode .cancel-button:hover {
  background-color: #555;
}
</style> 