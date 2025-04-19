<template>
  <div class="dashboard-layout">
    <TopBar :user="user" @open-logout-modal="isLogoutModalOpen = true" />
    <div class="dashboard-content">
      <UserSidebar @sidebar-toggle="onSidebarToggle" />
      <main class="dashboard-main">
        <RouterView />
      </main>
    </div>

    <!-- Logout Modal -->
    <div v-if="isLogoutModalOpen" class="modal-overlay" @click.self="closeModal">
      <div class="modal-container">
        <div class="modal-header">
          <font-awesome-icon icon="fa-solid fa-sign-out-alt" class="logout-icon" />
          <h2>خروج از حساب کاربری</h2>
        </div>
        <p>آیا مطمئن هستید که می‌خواهید از حساب کاربری خود خارج شوید؟</p>
        <div class="modal-actions">
          <button @click="logout" class="btn-danger">
            <font-awesome-icon icon="fa-solid fa-sign-out-alt" />
            خروج
          </button>
          <button @click="closeModal" class="btn-secondary">انصراف</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import UserSidebar from '@/components/user/UserSidebar.vue';
import TopBar from '@/components/common/TopBar.vue';

export default {
  name: 'DashboardLayout',
  components: {
    UserSidebar,
    TopBar
  },
  data() {
    return {
      isLogoutModalOpen: false
    }
  },
  computed: {
    ...mapState(['user'])
  },
  methods: {
    ...mapActions(['logoutUser']),
    async logout() {
      await this.logoutUser();
      this.$router.push('/');
    },
    closeModal() {
      this.isLogoutModalOpen = false;
    },
    onSidebarToggle(isCollapsed) {
      // You can handle sidebar collapse state here if needed
      // For example, add a class to the layout or store in state
    }
  },
  created() {
    if (!this.$store.getters.isAuthenticated) {
      this.$router.push('/login');
    }
  },
  mounted() {
    document.body.classList.add('dashboard-page');
  },
  beforeUnmount() {
    document.body.classList.remove('dashboard-page');
  }
}
</script>

<style scoped>
.dashboard-layout {
  min-height: 100vh;
  margin: 0;
  display: flex;
  flex-direction: column;
}

.dashboard-content {
  display: flex;
  flex: 1;
}

.dashboard-main {
  flex: 1;
  padding: 1.5rem;
  background-color: #f5f5f5;
  overflow-y: auto;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-container {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  width: 90%;
  max-width: 500px;
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.modal-header h2 {
  margin: 0;
  color: #333;
}

.logout-icon {
  color: #dc3545;
  font-size: 1.5rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-danger {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-danger:hover {
  background: #c82333;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: #e9ecef;
  color: #333;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-secondary:hover {
  background: #dee2e6;
}

body.dark-mode .dashboard-main {
  background-color: #222;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .dashboard-main {
    padding: 1rem;
  }

  .dashboard-content {
    flex-direction: column;
  }
}
</style> 