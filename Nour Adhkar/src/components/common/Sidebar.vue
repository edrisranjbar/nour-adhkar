<template>
  <aside class="sidebar" role="navigation" aria-label="پنل مدیریت">
    <div class="sidebar-header">
      <button class="collapse-btn" @click="toggleCollapsed" :aria-pressed="isCollapsed.toString()" aria-label="تغییر وضعیت نوار کناری">
        <font-awesome-icon :icon="isCollapsed ? 'fa-solid fa-angles-right' : 'fa-solid fa-angles-left'" />
      </button>
    </div>
    <nav class="nav">
      <ul class="menu">
        <li v-for="item in navigationItems" :key="item.path" class="menu-item">
          <RouterLink :to="item.path" class="menu-link" :class="{ active: isActive(item.path) }" :title="isCollapsed ? item.title : null">
            <div class="menu-icon">
              <font-awesome-icon :icon="item.icon" />
            </div>
            <span class="menu-text">{{ item.title }}</span>
          </RouterLink>
        </li>
      </ul>
    </nav>
    <div class="sidebar-footer" v-if="logoutItem">
      <button type="button" class="menu-link button-link logout-button" @click="handleLogout" :title="isCollapsed ? logoutItem.title : null">
        <div class="menu-icon">
          <font-awesome-icon :icon="logoutItem.icon" />
        </div>
        <span class="menu-text">{{ logoutItem.title }}</span>
      </button>
    </div>
  </aside>
</template>

<script>
export default {
  name: 'Sidebar',
  props: {
    items: {
      type: Array,
      required: true
    }
  },
  emits: ['sidebar-toggle', 'logout'],
  data() {
    return {
      isCollapsed: false,
      expandedMenus: {}
    }
  },
  computed: {
    logoutItem() {
      return this.items.find(item => item.action === 'logout');
    },
    navigationItems() {
      return this.items.filter(item => item.action !== 'logout');
    }
  },
  created() {
    // Restore persisted UI state
    try {
      const stored = localStorage.getItem('admin-sidebar-expanded');
      if (stored) this.expandedMenus = JSON.parse(stored);
      const collapsed = localStorage.getItem('admin-sidebar-collapsed');
      this.isCollapsed = collapsed === 'true';
      if (this.isCollapsed) document.body.classList.add('admin-collapsed');
    } catch (_) {}

    // Auto-expand the section that contains the active route
    this.autoExpandForRoute(this.$route.path);
  },
  watch: {
    isCollapsed(val) {
      try { localStorage.setItem('admin-sidebar-collapsed', String(val)); } catch (_) {}
    },
    '$route.path'(newPath) {
      // no-op for flat menu
    }
  },
  methods: {
    toggleCollapsed() {
      this.isCollapsed = !this.isCollapsed;
      document.body.classList.toggle('admin-collapsed', this.isCollapsed);
      this.$emit('sidebar-toggle', this.isCollapsed);
    },
    isActive(path) {
      if (!path) return false;
      // Exact for root, startsWith for sections
      return this.$route.path === path || this.$route.path.startsWith(path + '/');
    },
    autoExpandForRoute() {},
    handleLogout() {
      this.$emit('logout');
    }
  }
}
</script>

<style scoped>
.sidebar {
  width: 260px;
  background: #0f172a; /* --admin-header */
  color: #e5e7eb;      /* --admin-text */
  border-right: 1px solid rgba(255,255,255,0.08); /* --admin-border */
  overflow-y: auto;
  position: relative;
  backdrop-filter: blur(4px);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.sidebar-header {
  display: flex;
  justify-content: flex-end;
  padding: 0.5rem 0.75rem 0 0.75rem;
}

.collapse-btn {
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.08);
  color: #e5e7eb;
  padding: 0.35rem 0.55rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.collapse-btn:hover { background: rgba(255,255,255,0.12); color: #fff; }

.nav {
  padding: 1rem 0;
  flex: 1;
}

.menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.menu-item {
  margin-bottom: 0.25rem;
}

.menu-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #cbd5e1;
  text-decoration: none;
  transition: all 0.2s;
  border-left: 3px solid transparent;
  cursor: pointer;
  text-align: right;
  direction: rtl;
  width: 100%;
  background: none;
}

.button-link {
  border: none;
  background: none;
  width: 100%;
}

.menu-link:hover {
  background: rgba(255,255,255,0.06);
  color: #fff;
  border-left-color: #3b82f6; /* --admin-accent */
}

.menu-link.active {
  background: rgba(255,255,255,0.08);
  color: #fff;
  border-left-color: #3b82f6; /* --admin-accent */
}

.menu-link:focus-visible {
  outline: 2px solid #A79277;
  outline-offset: 2px;
}

.menu-icon {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.submenu-header {
  justify-content: space-between;
  direction: rtl;
}

.submenu-arrow {
  font-size: 0.8rem;
  transition: transform 0.2s;
  margin-right: auto;
  margin-left: 0.5rem;
}

.submenu {
  list-style: none;
  padding: 0;
  margin: 0;
  background: rgba(255,255,255,0.04);
}

.submenu-item .menu-link {
  padding-left: 1rem;
  padding-right: 2.5rem;
}

.submenu-item .menu-link:hover {
  background: rgba(255,255,255,0.06);
}

.submenu-item .menu-link.active {
  background: rgba(255,255,255,0.08);
}

/* Collapsed mode */
body.admin-collapsed .sidebar { width: 72px; }
body.admin-collapsed .sidebar .menu-text { display: none; }
body.admin-collapsed .sidebar .submenu { display: none !important; }
body.admin-collapsed .sidebar .submenu-header .submenu-arrow { display: none; }
body.admin-collapsed .sidebar .menu-link { justify-content: center; padding: 0.75rem; }

.sidebar-footer {
  margin-top: auto;
  padding: 1rem;
}

.logout-button {
  border: none;
  background: rgba(248, 113, 113, 0.12);
  color: #f87171;
  border-left: 3px solid transparent;
  transition: all 0.2s ease;
}

.logout-button:hover,
.logout-button:focus-visible {
  background: rgba(248, 113, 113, 0.2);
  border-left-color: #f87171;
  color: #fca5a5;
}
</style> 