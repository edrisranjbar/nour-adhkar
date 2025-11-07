<template>
  <div class="users-manage">
    <div class="admin-controls">
      <h1 class="page-title">مدیریت کاربران</h1>
      <button class="new-user-button" @click="openCreateModal">
        <font-awesome-icon icon="fa-solid fa-plus" />
        <span>کاربر جدید</span>
      </button>
    </div>
    
    <UserFilters 
      :initial-search-query="searchQuery"
      :initial-role-filter="roleFilter"
      :initial-sort-by="sortBy"
      @search="handleSearch"
      @filter-change="handleFilterChange"
    />
    
    <UserTable 
      :users="users"
      :loading="loading"
      :has-filters="hasActiveFilters"
      @edit-user="editUser"
      @toggle-status="toggleUserStatus"
      @delete-user="confirmAndDeleteUser"
    />
    
    <PaginationControls 
      :pagination="{
        current_page: pagination.current_page,
        last_page: pagination.last_page,
        total: pagination.total
      }"
      @page-change="changePage"
    />
    
    <UserEditModal 
      :show="showEditModal"
      :user="editedUser"
      :saving="saving"
      @close="closeModal"
      @save="saveUser"
    />
    
    <UserCreateModal
      :show="showCreateModal"
      :saving="saving"
      @close="closeCreateModal"
      @save="createNewUser"
    />
    
    <!-- Toast Notification -->
    <div v-if="toast.show" :class="['toast', `toast-${toast.type}`]">
      {{ toast.message }}
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import UserFilters from '@/components/Admin/UserFilters.vue';
import UserTable from '@/components/Admin/UserTable.vue';
import PaginationControls from '@/components/Admin/Pagination.vue';
import UserEditModal from '@/components/Admin/UserEditModal.vue';
import UserCreateModal from '@/components/Admin/UserCreateModal.vue';

export default {
  name: 'UsersManageView',
  components: {
    UserFilters,
    UserTable,
    PaginationControls,
    UserEditModal,
    UserCreateModal
  },
  data() {
    return {
      users: [],
      loading: true,
      searchQuery: '',
      roleFilter: '',
      sortBy: 'created_at_desc',
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
      },
      showEditModal: false,
      showCreateModal: false,
      editedUser: {
        id: null,
        name: '',
        email: '',
        role: 'user',
        active: true,
        password: ''
      },
      saving: false,
      // Add toast notification data
      toast: {
        show: false,
        message: '',
        type: 'success' // 'success' or 'error'
      }
    };
  },
  computed: {
    token() {
      return this.$store.state.token;
    },
    hasActiveFilters() {
      return !!this.searchQuery || !!this.roleFilter;
    }
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    // Add toast notification methods
    showToast(message, type = 'success') {
      this.toast.message = message;
      this.toast.type = type;
      this.toast.show = true;
      
      // Auto-hide after 3 seconds
      setTimeout(() => {
        this.toast.show = false;
      }, 3000);
    },
    
    async fetchUsers() {
      this.loading = true;
      console.log('Fetching users with params:', {
        page: this.pagination.current_page,
        per_page: this.pagination.per_page,
        search: this.searchQuery,
        role: this.roleFilter,
        sort: this.sortBy
      });
      
      try {
        const response = await axios.get(`${BASE_API_URL}/admin/users`, {
          params: {
            page: this.pagination.current_page,
            per_page: this.pagination.per_page,
            search: this.searchQuery,
            role: this.roleFilter,
            sort: this.sortBy
          },
          headers: {
            Authorization: `Bearer ${this.token}`
          }
        });
        
        console.log('Fetch response:', response.data);
        
        if (response.data.success) {
          this.users = response.data.users.data || [];
          this.pagination.current_page = response.data.users.current_page;
          this.pagination.last_page = response.data.users.last_page;
          this.pagination.total = response.data.users.total;
          console.log('Users updated:', this.users.length);
        } else {
          this.showToast('خطا در دریافت لیست کاربران', 'error');
        }
      } catch (error) {
        console.error('Error fetching users:', error);
        this.showToast('خطا در دریافت لیست کاربران', 'error');
      } finally {
        this.loading = false;
      }
    },
    
    handleSearch(query) {
      this.searchQuery = query;
      this.pagination.current_page = 1;
      this.fetchUsers();
    },
    
    handleFilterChange(filters) {
      this.searchQuery = filters.searchQuery;
      this.roleFilter = filters.roleFilter;
      this.sortBy = filters.sortBy;
      this.pagination.current_page = 1;
      this.fetchUsers();
    },
    
    changePage(page) {
      this.pagination.current_page = page;
      this.fetchUsers();
    },
    
    editUser(user) {
      this.editedUser = { ...user };
      delete this.editedUser.password; // Don't include password when editing
      this.showEditModal = true;
    },
    
    closeModal() {
      console.log('Closing modal');
      this.showEditModal = false;
      this.editedUser = {
        id: null,
        name: '',
        email: '',
        role: 'user',
        active: true,
        password: ''
      };
      // Ensure saving state is reset
      this.saving = false;
    },
    
    openCreateModal() {
      this.showCreateModal = true;
    },
    
    closeCreateModal() {
      this.showCreateModal = false;
    },
    
    async createNewUser(user) {
      this.saving = true;
      console.log('Creating new user:', user);
      
      try {
        const response = await axios.post(
          `${BASE_API_URL}/admin/users`, 
          user,
          {
            headers: {
              Authorization: `Bearer ${this.token}`
            }
          }
        );
        
        console.log('Create response:', response);
        
        if (response && response.data && response.data.success) {
          this.showToast('کاربر جدید با موفقیت ایجاد شد', 'success');
          
          // Close the modal first to provide better UX
          this.closeCreateModal();
          
          // Then fetch the updated data
          await this.fetchUsers();
        } else {
          // Show error message
          const errorMessage = 'خطا در ایجاد کاربر جدید';
          this.showToast(errorMessage, 'error');
        }
      } catch (error) {
        console.error('Error creating user:', error);
        
        // Show more specific error message if available
        const errorMessage = error.response?.data?.errors || 'خطا در ایجاد کاربر جدید';
        this.showToast(errorMessage, 'error');
        
        // Don't close the modal on error so user can fix the issue
      } finally {
        this.saving = false;
      }
    },
    
    async saveUser(user) {
      this.saving = true;
      console.log('Saving user:', user);
      
      try {
        let response;
        
        if (user.id) {
          // Update existing user
          response = await axios.put(
            `${BASE_API_URL}/admin/users/${user.id}`, 
            user,
            {
              headers: {
                Authorization: `Bearer ${this.token}`
              }
            }
          );
        } else {
          // Create new user
          response = await axios.post(
            `${BASE_API_URL}/admin/users`, 
            user,
            {
              headers: {
                Authorization: `Bearer ${this.token}`
              }
            }
          );
        }
        
        console.log('Save response:', response);
        
        // Use a simpler approach similar to toggleUserStatus
        if (response && response.data && response.data.success) {
          this.showToast(user.id ? 'کاربر با موفقیت ویرایش شد' : 'کاربر جدید با موفقیت ایجاد شد', 'success');
          
          // Close the modal first to provide better UX
          this.closeModal();
          
          // Then fetch the updated data
          await this.fetchUsers();
        } else {
          // Show error message
          const errorMessage = 'خطا در ذخیره اطلاعات کاربر';
          this.showToast(errorMessage, 'error');
        }
      } catch (error) {
        console.error('Error saving user:', error);
        
        // Show more specific error message if available
        const errorMessage = error.response?.data?.errors || 'خطا در ذخیره اطلاعات کاربر';
        this.showToast(errorMessage, 'error');
        
        // Don't close the modal on error so user can fix the issue
      } finally {
        this.saving = false;
      }
    },
    
    async toggleUserStatus(user) {
      try {
        const response = await axios.patch(
          `${BASE_API_URL}/admin/users/${user.id}/toggle-status`,
          {},
          {
            headers: {
              Authorization: `Bearer ${this.token}`
            }
          }
        );
        
        if (response.data.success) {
          this.showToast(`کاربر با موفقیت ${user.active ? 'غیرفعال' : 'فعال'} شد`, 'success');
          this.fetchUsers();
        } else {
          this.showToast(response.data.message || 'خطا در تغییر وضعیت کاربر', 'error');
        }
      } catch (error) {
        console.error('Error toggling user status:', error);
        this.showToast(error.response?.data?.message || 'خطا در تغییر وضعیت کاربر', 'error');
      }
    },

    async confirmAndDeleteUser(user) {
      const confirmed = window.confirm(`آیا از حذف کاربر «${user.name}» مطمئن هستید؟ این عملیات غیرقابل بازگشت است.`);
      if (!confirmed) return;

      try {
        const response = await axios.delete(
          `${BASE_API_URL}/admin/users/${user.id}`,
          {
            headers: {
              Authorization: `Bearer ${this.token}`
            }
          }
        );

        if (response.data && response.data.success) {
          this.showToast('کاربر با موفقیت حذف شد', 'success');
          // Refresh list (keep current page; adjust if last item on page)
          if (this.users.length === 1 && this.pagination.current_page > 1) {
            this.pagination.current_page -= 1;
          }
          await this.fetchUsers();
        } else {
          this.showToast(response.data?.message || 'خطا در حذف کاربر', 'error');
        }
      } catch (error) {
        const message = error.response?.data?.message || 'خطا در حذف کاربر';
        this.showToast(message, 'error');
      }
    }
  }
};
</script>

<style scoped>
.users-manage {
  min-height: 100vh;
  background: var(--admin-bg);
  color: var(--admin-text);
  padding: 1.5rem 2rem 2.5rem;
}

.admin-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.page-title {
  margin: 0;
  font-size: 1.8rem;
  color: var(--admin-text);
}

.new-user-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.25rem;
  background-color: var(--admin-accent);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-family: inherit;
  font-size: 0.95rem;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.new-user-button:hover {
  background-color: rgba(59, 130, 246, 0.85);
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.25);
}

.new-user-button span {
  font-weight: 600;
}

/* Toast Notification Styles */
.toast {
  position: fixed;
  bottom: 20px;
  right: 20px;
  padding: 12px 20px;
  border-radius: 8px;
  color: #fff;
  font-weight: 500;
  z-index: 1000;
  animation: fadeIn 0.3s, fadeOut 0.3s 2.7s;
  max-width: 360px;
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.4);
}

.toast-success {
  background-color: #4ade80;
  color: #0f172a;
}

.toast-error {
  background-color: #f87171;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeOut {
  from { opacity: 1; transform: translateY(0); }
  to { opacity: 0; transform: translateY(20px); }
}

/* Dark mode styles */
body.dark-mode .page-title {
  color: #eee;
}

body.dark-mode .toast {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

body.dark-mode .new-user-button {
  background-color: #967f69;
}

body.dark-mode .new-user-button:hover {
  background-color: #A79277;
}
</style> 
