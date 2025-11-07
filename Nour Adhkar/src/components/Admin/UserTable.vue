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

            <button 
              class="action-button delete-button" 
              title="حذف کاربر"
              @click="$emit('delete-user', user)"
            >
              <font-awesome-icon icon="fa-solid fa-trash" />
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
  faCheck,
  faTrash
} from '@fortawesome/free-solid-svg-icons';

// Add icons to the library
library.add(
  faUsers, 
  faPenToSquare, 
  faBan, 
  faCheck,
  faTrash
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
.users-list-container {
  width: 100%;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 12px;
  overflow: hidden;
  background: var(--admin-surface);
  border: 1px solid var(--admin-border);
  box-shadow: 0 16px 48px rgba(15, 23, 42, 0.45);
}

.users-table th,
.users-table td {
  padding: 0.9rem 1rem;
  text-align: right;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
}

.users-table th {
  font-weight: 600;
  color: var(--admin-muted);
  background-color: rgba(255, 255, 255, 0.05);
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
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
  border: 1px solid rgba(255, 255, 255, 0.1);
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
  background-color: var(--admin-accent);
  color: #0f172a;
  font-size: 1rem;
  font-weight: 700;
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
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  text-align: center;
}

.admin-badge {
  background-color: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
}

.user-badge {
  background-color: rgba(148, 163, 184, 0.12);
  color: var(--admin-muted);
}

.active-badge {
  background-color: rgba(74, 222, 128, 0.15);
  color: #4ade80;
}

.inactive-badge {
  background-color: rgba(248, 113, 113, 0.15);
  color: #f87171;
}

.actions-cell {
  white-space: nowrap;
}

.action-button {
  background: none;
  border: none;
  padding: 0.4rem 0.5rem;
  cursor: pointer;
  font-size: 0.95rem;
  color: var(--admin-muted);
  transition: color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
  border-radius: 6px;
}

.action-button:hover {
  color: var(--admin-accent);
  background-color: rgba(59, 130, 246, 0.12);
  transform: translateY(-1px);
}

.deactivate-button:hover {
  color: #f87171;
}

.activate-button:hover {
  color: #4ade80;
}

.admin-user {
  background-color: rgba(167, 146, 119, 0.08);
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 0;
  color: var(--admin-muted);
  text-align: center;
}

.spinner {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  border: 2px solid rgba(59, 130, 246, 0.2);
  border-top: 2px solid var(--admin-accent);
  border-radius: 50%;
  margin-bottom: 1rem;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 3rem;
  color: var(--admin-border);
  margin-bottom: 1rem;
}
</style> 