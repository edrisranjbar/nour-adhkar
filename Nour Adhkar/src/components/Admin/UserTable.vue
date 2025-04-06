<template>
  <div class="users-list-container">
    <!-- Loading state -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <span>در حال بارگذاری کاربران...</span>
    </div>
    
    <!-- Empty state -->
    <div v-else-if="!users.length" class="empty-state">
      <font-awesome-icon icon="fa-solid fa-users" class="empty-icon" />
      <p v-if="hasFilters">هیچ کاربری با این فیلترها یافت نشد.</p>
      <p v-else>هیچ کاربری در سیستم ثبت نشده است.</p>
    </div>
    
    <!-- Users table -->
    <table v-else class="users-table">
      <thead>
        <tr>
          <th>نام کاربر</th>
          <th>ایمیل</th>
          <th>نقش</th>
          <th>تاریخ عضویت</th>
          <th>وضعیت</th>
          <th>عملیات</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id" :class="{ 'admin-user': user.role === 'admin' }">
          <td class="user-info-cell">
            <div class="user-avatar">
              <img v-if="user.avatar" :src="user.avatar" :alt="user.name">
              <div v-else class="avatar-placeholder">{{ getUserInitials(user.name) }}</div>
            </div>
            <span class="user-name">{{ user.name }}</span>
          </td>
          <td>{{ user.email }}</td>
          <td>
            <span :class="['role-badge', user.role === 'admin' ? 'admin-badge' : 'user-badge']">
              {{ user.role === 'admin' ? 'مدیر' : 'کاربر' }}
            </span>
          </td>
          <td>{{ formatDate(user.created_at) }}</td>
          <td>
            <span :class="['status-badge', user.active ? 'active-badge' : 'inactive-badge']">
              {{ user.active ? 'فعال' : 'غیرفعال' }}
            </span>
          </td>
          <td class="actions-cell">
            <button 
              class="action-button edit-button" 
              title="ویرایش کاربر"
              @click="$emit('edit-user', user)"
            >
              <font-awesome-icon icon="fa-solid fa-pen-to-square" />
            </button>
            
            <button 
              v-if="user.active" 
              class="action-button deactivate-button" 
              title="غیرفعال کردن کاربر"
              @click="$emit('toggle-status', user)"
            >
              <font-awesome-icon icon="fa-solid fa-ban" />
            </button>
            
            <button 
              v-else 
              class="action-button activate-button" 
              title="فعال کردن کاربر"
              @click="$emit('toggle-status', user)"
            >
              <font-awesome-icon icon="fa-solid fa-check" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { library } from '@fortawesome/fontawesome-svg-core';
import { 
  faUsers, 
  faPenToSquare, 
  faBan, 
  faCheck 
} from '@fortawesome/free-solid-svg-icons';

// Add icons to the library
library.add(
  faUsers, 
  faPenToSquare, 
  faBan, 
  faCheck
);

export default {
  name: 'UserTable',
  props: {
    users: {
      type: Array,
      required: true
    },
    loading: {
      type: Boolean,
      default: false
    },
    hasFilters: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    getUserInitials(name) {
      if (!name) return '';
      return name.split(' ')
        .map(word => word[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
    },
    
    formatDate(dateString) {
      if (!dateString) return '-';
      
      // Convert to Persian date
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(date);
    }
  }
};
</script>

<style scoped>
.users-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1.5rem;
}

.users-table th,
.users-table td {
  padding: 0.75rem;
  text-align: right;
  border-bottom: 1px solid #eee;
}

.users-table th {
  font-weight: 600;
  color: #555;
  background-color: #f8f9fa;
}

.user-info-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #A79277;
  color: white;
  font-size: 1rem;
  font-weight: 600;
}

.user-name {
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.role-badge,
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
  text-align: center;
}

.admin-badge {
  background-color: #A79277;
  color: white;
}

.user-badge {
  background-color: #f2f2f2;
  color: #777;
}

.active-badge {
  background-color: #e6f7ed;
  color: #28a745;
}

.inactive-badge {
  background-color: #fef2f2;
  color: #ef4444;
}

.actions-cell {
  white-space: nowrap;
}

.action-button {
  background: none;
  border: none;
  padding: 0.4rem 0.5rem;
  cursor: pointer;
  font-size: 0.9rem;
  color: #666;
  transition: color 0.2s;
}

.edit-button:hover {
  color: #A79277;
}

.deactivate-button:hover {
  color: #ef4444;
}

.activate-button:hover {
  color: #28a745;
}

.admin-user {
  background-color: #fdfbf8;
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 0;
  color: #777;
  text-align: center;
}

.spinner {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  border: 2px solid #f3f3f3;
  border-top: 2px solid #A79277;
  border-radius: 50%;
  margin-bottom: 1rem;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 3rem;
  color: #ddd;
  margin-bottom: 1rem;
}

/* Dark mode styles */
body.dark-mode .users-table th {
  background-color: #333;
  color: #bbb;
}

body.dark-mode .users-table td {
  border-bottom-color: #444;
}

body.dark-mode .user-badge {
  background-color: #444;
  color: #aaa;
}

body.dark-mode .active-badge {
  background-color: #1a3b29;
  color: #4ade80;
}

body.dark-mode .inactive-badge {
  background-color: #3b1a1a;
  color: #f87171;
}

body.dark-mode .action-button {
  color: #aaa;
}

body.dark-mode .admin-user {
  background-color: #2a2620;
}
</style> 