<template>
  <aside class="sidebar">
    <nav class="nav">
      <ul class="menu">
        <li v-for="item in items" :key="item.id || item.path" class="menu-item" :class="{ 'has-submenu': item.items }">
          <template v-if="item.items">
            <div class="menu-link submenu-header" @click="toggleSubmenu(item.id)">
              <div class="menu-icon">
                <font-awesome-icon :icon="item.icon" />
              </div>
              <span class="menu-text">{{ item.title }}</span>
              <font-awesome-icon 
                :icon="expandedMenus.includes(item.id) ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" 
                class="submenu-arrow"
              />
            </div>
            <ul v-show="expandedMenus.includes(item.id)" class="submenu">
              <li v-for="subItem in item.items" :key="subItem.path" class="submenu-item">
                <RouterLink :to="subItem.path" class="menu-link" active-class="active">
                  <div class="menu-icon">
                    <font-awesome-icon :icon="subItem.icon" />
                  </div>
                  <span class="menu-text">{{ subItem.title }}</span>
                </RouterLink>
              </li>
            </ul>
          </template>
          <RouterLink v-else :to="item.path" class="menu-link" active-class="active" :exact="item.exact">
            <div class="menu-icon">
              <font-awesome-icon :icon="item.icon" />
            </div>
            <span class="menu-text">{{ item.title }}</span>
          </RouterLink>
        </li>
      </ul>
    </nav>
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
  data() {
    return {
      expandedMenus: []
    }
  },
  methods: {
    toggleSubmenu(menuId) {
      const index = this.expandedMenus.indexOf(menuId);
      if (index === -1) {
        this.expandedMenus.push(menuId);
      } else {
        this.expandedMenus.splice(index, 1);
      }
    }
  }
}
</script>

<style scoped>
.sidebar {
  width: 250px;
  background-color: #333;
  color: white;
  border-right: 1px solid #444;
  overflow-y: auto;
  position: relative;
}

.nav {
  padding: 1rem 0;
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
  color: #ddd;
  text-decoration: none;
  transition: all 0.2s;
  border-left: 3px solid transparent;
  cursor: pointer;
  text-align: right;
  direction: rtl;
}

.menu-link:hover {
  background-color: #444;
  color: white;
  border-left-color: #A79277;
}

.menu-link.active {
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
  background-color: #2a2a2a;
}

.submenu-item .menu-link {
  padding-left: 1rem;
  padding-right: 2.5rem;
}

.submenu-item .menu-link:hover {
  background-color: #3a3a3a;
}

.submenu-item .menu-link.active {
  background-color: #3a3a3a;
}
</style> 