<template>
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
</template>

<script>
export default {
  name: 'AdminSidebar',
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
  methods: {
    toggleSidebar() {
      this.isSidebarCollapsed = !this.isSidebarCollapsed;
      this.$emit('sidebar-toggle', this.isSidebarCollapsed);
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
        users: ['/admin/users'],
        system: ['/admin/settings', '/admin/logs']
      };
      
      return categoryRoutes[category]?.some(path => route.startsWith(path)) || false;
    }
  },
  created() {
    // Auto-expand category based on current route
    Object.keys(this.expandedCategories).forEach(category => {
      if (this.isRouteInCategory(category)) {
        this.expandedCategories[category] = true;
      }
    });
  }
}
</script>

<style scoped>
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