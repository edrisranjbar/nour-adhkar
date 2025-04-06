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
      <aside class="admin-sidebar" :class="{ 'collapsed': isSidebarCollapsed }">
        <div class="sidebar-header">
          <button class="collapse-button" @click="toggleSidebar">
            <font-awesome-icon :icon="isSidebarCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'" />
          </button>
        </div>
        <nav class="admin-nav">
          <ul class="admin-menu">
            <li class="admin-menu-item">
              <RouterLink to="/admin" class="admin-menu-link" active-class="active" exact>
                <div class="menu-icon">
                  <font-awesome-icon icon="fa-solid fa-tachometer-alt" />
                </div>
                <span class="menu-text">پیشخوان</span>
              </RouterLink>
            </li>
            <li class="admin-menu-item">
              <RouterLink to="/admin/blog" class="admin-menu-link" active-class="active">
                <div class="menu-icon">
                  <font-awesome-icon icon="fa-solid fa-newspaper" />
                </div>
                <span class="menu-text">مدیریت مقالات</span>
              </RouterLink>
            </li>
            <li class="admin-menu-item">
              <RouterLink to="/admin/categories" class="admin-menu-link" active-class="active">
                <div class="menu-icon">
                  <font-awesome-icon icon="fa-solid fa-tags" />
                </div>
                <span class="menu-text">دسته‌بندی‌ها</span>
              </RouterLink>
            </li>
            <li class="admin-menu-item">
              <RouterLink to="/admin/users" class="admin-menu-link" active-class="active">
                <div class="menu-icon">
                  <font-awesome-icon icon="fa-solid fa-users" />
                </div>
                <span class="menu-text">مدیریت کاربران</span>
              </RouterLink>
            </li>
          </ul>
        </nav>
      </aside>
      <main class="admin-main">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
  name: 'AdminLayout',
  data() {
    return {
      isSidebarCollapsed: false
    }
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
    },
    toggleSidebar() {
      this.isSidebarCollapsed = !this.isSidebarCollapsed;
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
}

.admin-sidebar {
  width: 250px;
  background-color: #333;
  color: white;
  border-right: 1px solid #444;
  overflow-y: auto;
  transition: width 0.3s ease;
  position: relative;
}

.admin-sidebar.collapsed {
  width: 70px;
}

.sidebar-header {
  padding: 1rem;
  display: flex;
  justify-content: flex-end;
  border-bottom: 1px solid #444;
}

.collapse-button {
  background: none;
  border: none;
  color: #aaa;
  cursor: pointer;
  padding: 8px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.collapse-button:hover {
  color: white;
  background-color: rgba(255, 255, 255, 0.1);
}

.admin-nav {
  padding: 1rem 0;
}

.admin-menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.admin-menu-item {
  margin-bottom: 0.25rem;
}

.admin-menu-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #ddd;
  text-decoration: none;
  transition: all 0.2s;
  border-left: 3px solid transparent;
}

.admin-menu-link:hover {
  background-color: #444;
  color: white;
  border-left-color: #A79277;
}

.admin-menu-link.active {
  background-color: #444;
  color: white;
  border-left-color: #A79277;
}

.menu-icon {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.collapsed .menu-text {
  display: none;
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

/* Responsive */
@media (max-width: 768px) {
  .admin-content {
    flex-direction: column;
  }

  .admin-sidebar {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid #444;
  }

  .admin-sidebar.collapsed {
    width: 100%;
  }

  .collapsed .menu-text {
    display: block;
  }

  .admin-menu {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.5rem;
  }

  .admin-menu-item {
    margin-bottom: 0;
    flex: 1;
    min-width: 150px;
  }

  .admin-menu-link {
    justify-content: center;
    text-align: center;
    border-left: none;
    border-bottom: 3px solid transparent;
    border-radius: 4px;
  }

  .admin-menu-link:hover,
  .admin-menu-link.active {
    border-left-color: transparent;
    border-bottom-color: #A79277;
  }
}
</style>