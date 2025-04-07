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

            <!-- Content Management Category -->
            <li class="admin-menu-category">
              <div class="category-header" @click="toggleCategory('content')">
                <div class="menu-icon">
                  <font-awesome-icon icon="fa-solid fa-file-alt" />
                </div>
                <span class="menu-text">مدیریت محتوا</span>
                <div class="category-toggle" v-if="!isSidebarCollapsed">
                  <font-awesome-icon :icon="expandedCategories.content ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-left'" />
                </div>
              </div>
              <transition name="slide">
                <ul class="category-items" v-if="expandedCategories.content || isSidebarCollapsed">
                  <li class="admin-menu-item">
                    <RouterLink to="/admin/blog" class="admin-menu-link" active-class="active">
                      <div class="menu-icon">
                        <font-awesome-icon icon="fa-solid fa-newspaper" />
                      </div>
                      <span class="menu-text">مقالات</span>
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
                    <RouterLink to="/admin/media" class="admin-menu-link" active-class="active">
                      <div class="menu-icon">
                        <font-awesome-icon icon="fa-solid fa-images" />
                      </div>
                      <span class="menu-text">رسانه‌ها</span>
                    </RouterLink>
                  </li>
                </ul>
              </transition>
            </li>

            <!-- User Management Category -->
            <li class="admin-menu-category">
              <div class="category-header" @click="toggleCategory('users')">
                <div class="menu-icon">
                  <font-awesome-icon icon="fa-solid fa-user-shield" />
                </div>
                <span class="menu-text">مدیریت کاربران</span>
                <div class="category-toggle" v-if="!isSidebarCollapsed">
                  <font-awesome-icon :icon="expandedCategories.users ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-left'" />
                </div>
              </div>
              <transition name="slide">
                <ul class="category-items" v-if="expandedCategories.users || isSidebarCollapsed">
                  <li class="admin-menu-item">
                    <RouterLink to="/admin/users" class="admin-menu-link" active-class="active">
                      <div class="menu-icon">
                        <font-awesome-icon icon="fa-solid fa-users" />
                      </div>
                      <span class="menu-text">کاربران</span>
                    </RouterLink>
                  </li>
                  <li class="admin-menu-item">
                    <RouterLink to="/admin/permissions" class="admin-menu-link" active-class="active">
                      <div class="menu-icon">
                        <font-awesome-icon icon="fa-solid fa-lock" />
                      </div>
                      <span class="menu-text">دسترسی‌ها</span>
                    </RouterLink>
                  </li>
                </ul>
              </transition>
            </li>
            
            <!-- System Category -->
            <li class="admin-menu-category">
              <div class="category-header" @click="toggleCategory('system')">
                <div class="menu-icon">
                  <font-awesome-icon icon="fa-solid fa-cogs" />
                </div>
                <span class="menu-text">تنظیمات سیستم</span>
                <div class="category-toggle" v-if="!isSidebarCollapsed">
                  <font-awesome-icon :icon="expandedCategories.system ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-left'" />
                </div>
              </div>
              <transition name="slide">
                <ul class="category-items" v-if="expandedCategories.system || isSidebarCollapsed">
                  <li class="admin-menu-item">
                    <RouterLink to="/admin/settings" class="admin-menu-link" active-class="active">
                      <div class="menu-icon">
                        <font-awesome-icon icon="fa-solid fa-sliders-h" />
                      </div>
                      <span class="menu-text">تنظیمات عمومی</span>
                    </RouterLink>
                  </li>
                  <li class="admin-menu-item">
                    <RouterLink to="/admin/backup" class="admin-menu-link" active-class="active">
                      <div class="menu-icon">
                        <font-awesome-icon icon="fa-solid fa-database" />
                      </div>
                      <span class="menu-text">پشتیبان‌گیری</span>
                    </RouterLink>
                  </li>
                  <li class="admin-menu-item">
                    <RouterLink to="/admin/logs" class="admin-menu-link" active-class="active">
                      <div class="menu-icon">
                        <font-awesome-icon icon="fa-solid fa-clipboard-list" />
                      </div>
                      <span class="menu-text">گزارش‌ها</span>
                    </RouterLink>
                  </li>
                </ul>
              </transition>
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
      isSidebarCollapsed: false,
      expandedCategories: {
        content: true,
        users: false,
        system: false
      }
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
    },
    toggleCategory(category) {
      if (!this.isSidebarCollapsed) {
        this.expandedCategories[category] = !this.expandedCategories[category];
      }
    },
    isRouteInCategory(category) {
      const route = this.$route.path;
      const categoryRoutes = {
        content: ['/admin/blog', '/admin/categories', '/admin/media'],
        users: ['/admin/users', '/admin/permissions'],
        system: ['/admin/settings', '/admin/backup', '/admin/logs']
      };
      
      return categoryRoutes[category]?.some(path => route.startsWith(path)) || false;
    }
  },
  created() {
    if (!this.$store.getters.isAdmin) {
      this.$router.push('/');
    }
    
    // Auto-expand category based on current route
    Object.keys(this.expandedCategories).forEach(category => {
      if (this.isRouteInCategory(category)) {
        this.expandedCategories[category] = true;
      }
    });
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

/* Category styles */
.admin-menu-category {
  margin-bottom: 0.25rem;
}

.category-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #ddd;
  cursor: pointer;
  transition: all 0.2s;
  border-left: 3px solid transparent;
  position: relative;
}

.category-header:hover {
  background-color: #3c3c3c;
  color: white;
}

.category-toggle {
  margin-left: auto;
  font-size: 0.8rem;
  transition: transform 0.2s ease;
}

.expandedCategories.content .category-header .category-toggle {
  transform: rotate(90deg);
}

.category-items {
  list-style: none;
  padding: 0;
  margin: 0;
  overflow: hidden;
}

.category-items .admin-menu-item {
  margin-bottom: 0.1rem;
}

.category-items .admin-menu-link {
  padding-left: 2.5rem;
  background-color: #2a2a2a;
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
}

.slide-enter-to,
.slide-leave-from {
  max-height: 300px;
  opacity: 1;
}

/* Collapsed sidebar adjustments for categories */
.collapsed .category-header {
  padding: 0.75rem 1rem;
  justify-content: center;
}

.collapsed .category-toggle {
  display: none;
}

.collapsed .category-items {
  position: absolute;
  left: 70px;
  top: 0;
  width: 200px;
  background-color: #333;
  border-radius: 0 4px 4px 0;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2);
  z-index: 10;
  display: none;
}

.collapsed .admin-menu-category:hover .category-items {
  display: block;
}

.collapsed .category-items .admin-menu-link {
  padding-left: 1rem;
}

/* Mobile responsive adjustments for categories */
@media (max-width: 768px) {
  .admin-menu-category {
    flex: 1;
    min-width: 150px;
  }
  
  .category-header {
    justify-content: center;
    text-align: center;
    border-radius: 4px;
    border-left: none;
    border-bottom: 3px solid transparent;
    margin-bottom: 0.5rem;
  }

  .category-items {
    position: static;
    width: 100%;
    box-shadow: none;
    background-color: transparent;
  }
  
  .category-toggle {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
  }
  
  .category-items .admin-menu-link {
    padding-left: 1rem;
    background-color: transparent;
  }
}
</style>