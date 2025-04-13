<template>
  <div class="admin-layout">
    <header class="admin-header">
      <div class="admin-header-container">
        <div class="admin-brand">
          <RouterLink to="/admin" class="admin-logo">
            <img src="@/assets/icons/logo.png" alt="Logo" class="logo-image" />
            <span class="logo-text">اذکار نور</span>
            <span class="admin-badge">مدیریت</span>
          </RouterLink>
        </div>
        <div class="admin-user-info">
          <div class="user-avatar">
            <div v-if="!user?.avatar" class="avatar-placeholder">
              {{ userInitials }}
            </div>
            <img v-else :src="user.avatar" :alt="user.name" />
          </div>
          <div class="admin-user-name">
            <span class="user-name">{{ user?.name || 'Admin' }}</span>
            <button class="logout-button" @click="logout">
              <font-awesome-icon icon="fa-solid fa-sign-out-alt" />
              <span>خروج</span>
            </button>
          </div>
        </div>
      </div>
    </header>
    <div class="admin-content">
      <AdminSidebar />
      <main class="admin-main">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import AdminSidebar from '@/components/Admin/AdminSidebar.vue';

export default {
  name: 'AdminLayout',
  components: {
    AdminSidebar
  },
  computed: {
    ...mapState(['user']),
    userInitials() {
      if (!this.user || !this.user.name) return '';
      return this.user.name.split(' ')
        .map(word => word[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
    }
  },
  methods: {
    ...mapActions(['logoutUser']),
    async logout() {
      await this.logoutUser();
      this.$router.push('/');
    }
  },
  created() {
    if (!this.$store.getters.isAdmin) {
      this.$router.push('/');
    }
  },
  mounted() {
    document.body.classList.add('admin-page');
  },
  beforeUnmount() {
    document.body.classList.remove('admin-page');
  }
}
</script>

<style scoped>
.admin-layout {
  min-height: 100vh;
  margin: 0;
  display: flex;
  flex-direction: column;
}

.admin-header {
  margin-top: 0;
  border-radius: 0;
  background-color: #262626;
  color: white;
  border-bottom: 1px solid #444;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  z-index: 100;
}

.admin-header-container {
  padding: 0.75rem 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.admin-content {
  display: flex;
  flex: 1;
}

.admin-brand {
  display: flex;
  align-items: center;
}

.admin-logo {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: white;
  margin-left: 1rem;
  gap: 0.75rem;
}

.logo-image {
  width: 32px;
  height: 32px;
  margin-left: 0.5rem;
  transition: transform 0.3s ease;
}

.admin-logo:hover .logo-image {
  transform: rotate(5deg);
}

.logo-text {
  font-size: 1.2rem;
  font-weight: 600;
  margin-right: 0.5rem;
}

.admin-badge {
  color: white;
  background-color: #A79277;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

.admin-user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #A79277;
  color: white;
  font-size: 1.2rem;
  font-weight: 600;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.admin-user-name {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-name {
  font-weight: 500;
  color: #fff;
}

.logout-button {
  background: none;
  border: none;
  color: #aaa;
  cursor: pointer;
  padding: 0;
  font-family: inherit;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  transition: color 0.2s ease;
}

.logout-button:hover {
  color: white;
}

.admin-main {
  flex: 1;
  padding: 1.5rem;
  background-color: #f5f5f5;
  overflow-y: auto;
}

body.dark-mode .admin-main {
  background-color: #222;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .admin-header-container {
    padding: 0.5rem;
  }

  .logo-text {
    display: none;
  }

  .admin-badge {
    display: none;
  }

  .user-name {
    display: none;
  }

  .admin-user-info {
    gap: 0.5rem;
  }

  .user-avatar {
    width: 32px;
    height: 32px;
  }

  .admin-content {
    flex-direction: column;
  }
}
</style>