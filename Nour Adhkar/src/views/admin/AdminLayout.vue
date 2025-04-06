<template>
  <div class="admin-layout">
    <header class="admin-header">
      <div class="admin-header-container">
        <div class="admin-brand">
          <RouterLink to="/" class="admin-logo">
            <img src="@/assets/icons/logo.png" alt="اذکار نور" class="logo-image">
            <span class="logo-text">اذکار نور</span>
          </RouterLink>
          <span class="admin-badge">پنل مدیریت</span>
        </div>
        
        <div class="admin-user-info">
          <div class="user-avatar" v-if="user">
            <img v-if="user.avatar" :src="user.avatar" :alt="user.name">
            <div v-else class="avatar-placeholder">{{ userInitials }}</div>
          </div>
          <div class="admin-user-name" v-if="user">
            <span>{{ user.name }}</span>
            <button @click="logout" class="logout-button">
              <font-awesome-icon icon="fa-solid fa-sign-out-alt" />
              خروج
            </button>
          </div>
        </div>
      </div>
    </header>
    
    <div class="admin-content">
      <aside class="admin-sidebar">
        <nav class="admin-nav">
          <ul class="admin-menu">
            <li class="admin-menu-item">
              <RouterLink to="/admin" class="admin-menu-link" active-class="active" exact>
                <font-awesome-icon icon="fa-solid fa-tachometer-alt" />
                <span>پیشخوان</span>
              </RouterLink>
            </li>
            <li class="admin-menu-item">
              <RouterLink to="/admin/blog" class="admin-menu-link" active-class="active">
                <font-awesome-icon icon="fa-solid fa-newspaper" />
                <span>مدیریت مقالات</span>
              </RouterLink>
            </li>
            <!-- Add other admin menu items as needed -->
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
    // Verify that user is admin, redirect if not
    if (!this.$store.getters.isAdmin) {
      this.$router.push('/');
    }
  },
  mounted() {
    // Add the admin-page class to body when this component mounts
    document.body.classList.add('admin-page');
  },
  beforeUnmount() {
    // Remove the admin-page class from body when this component unmounts
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
}

.logo-image {
  width: 32px;
  height: 32px;
  margin-left: 0.5rem;
}

.logo-text {
  font-size: 1.2rem;
  font-weight: 600;
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
  gap: 0.75rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;
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
  font-size: 1.2rem;
  padding-top: 0.4rem;
  font-weight: 600;
}

.admin-user-name {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
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
}

.logout-button:hover {
  color: white;
}

.admin-content {
  display: flex;
  flex: 1;
}

.admin-sidebar {
  width: 250px;
  background-color: #333;
  color: white;
  border-right: 1px solid #444;
  overflow-y: auto;
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
}

.admin-menu-link:hover {
  background-color: #444;
  color: white;
}

.admin-menu-link.active {
  background-color: #A79277;
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
  
  .admin-menu {
    display: flex;
    overflow-x: auto;
  }
  
  .admin-menu-item {
    margin-bottom: 0;
  }
  
  .admin-menu-link {
    white-space: nowrap;
  }
}
</style> 