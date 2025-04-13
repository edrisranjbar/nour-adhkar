<template>
  <aside class="sidebar" :class="{ 'collapsed': isCollapsed }">
    <div class="sidebar-header">
      <button class="collapse-button" @click="toggleSidebar">
        <font-awesome-icon :icon="isCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'" />
      </button>
    </div>
    <nav class="nav">
      <ul class="menu">
        <!-- Render all items in their original order -->
        <template v-for="item in items">
          <!-- Single items -->
          <li v-if="!item.items" :key="item.path" class="menu-item">
            <RouterLink :to="item.path" class="menu-link" active-class="active" :exact="item.exact">
              <div class="menu-icon">
                <font-awesome-icon :icon="item.icon" />
              </div>
              <span class="menu-text">{{ item.title }}</span>
            </RouterLink>
          </li>

          <!-- Categories -->
          <li v-else :key="item.id" class="menu-category">
            <div class="category-header" @click="toggleCategory(item.id)">
              <div class="menu-icon">
                <font-awesome-icon :icon="item.icon" />
              </div>
              <span class="menu-text">{{ item.title }}</span>
              <div class="category-toggle" v-if="!isCollapsed">
                <font-awesome-icon :icon="expandedCategories[item.id] ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-left'" />
              </div>
            </div>
            <transition name="slide">
              <ul class="category-items" v-if="expandedCategories[item.id] || isCollapsed">
                <li v-for="subItem in item.items" :key="subItem.path" class="menu-item">
                  <RouterLink :to="subItem.path" class="menu-link" active-class="active">
                    <div class="menu-icon">
                      <font-awesome-icon :icon="subItem.icon" />
                    </div>
                    <span class="menu-text">{{ subItem.title }}</span>
                  </RouterLink>
                </li>
              </ul>
            </transition>
          </li>
        </template>
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
      required: true,
      // Each item should have: { path, title, icon, exact? }
      // Categories should have: { id, title, icon, items: [{ path, title, icon }] }
    }
  },
  data() {
    return {
      isCollapsed: false,
      expandedCategories: {}
    }
  },
  computed: {
    singleItems() {
      return this.items.filter(item => !item.items);
    },
    categories() {
      return this.items.filter(item => item.items);
    }
  },
  methods: {
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed;
      this.$emit('sidebar-toggle', this.isCollapsed);
    },
    toggleCategory(categoryId) {
      if (!this.isCollapsed) {
        this.expandedCategories[categoryId] = !this.expandedCategories[categoryId];
      }
    },
    isRouteInCategory(category) {
      const route = this.$route.path;
      return category.items.some(item => route.startsWith(item.path));
    },
    initializeExpandedCategories() {
      const newExpandedCategories = {};
      this.categories.forEach(category => {
        newExpandedCategories[category.id] = this.isRouteInCategory(category);
      });
      this.expandedCategories = newExpandedCategories;
    }
  },
  created() {
    this.initializeExpandedCategories();
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
  transition: width 0.3s ease;
  position: relative;
}

.sidebar.collapsed {
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

.collapsed .menu-text {
  display: none;
}

/* Category styles */
.menu-category {
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

.category-items {
  list-style: none;
  padding: 0;
  margin: 0;
  overflow: hidden;
}

.category-items .menu-item {
  margin-bottom: 0.1rem;
}

.category-items .menu-link {
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

/* Collapsed sidebar adjustments */
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

.collapsed .menu-category:hover .category-items {
  display: block;
}

.collapsed .category-items .menu-link {
  padding-left: 1rem;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid #444;
  }

  .sidebar.collapsed {
    width: 100%;
  }

  .collapsed .menu-text {
    display: block;
  }

  .menu {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.5rem;
  }

  .menu-item {
    margin-bottom: 0;
    flex: 1;
    min-width: 150px;
  }

  .menu-category {
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
  
  .category-items .menu-link {
    padding-left: 1rem;
    background-color: transparent;
  }

  .menu-link {
    justify-content: center;
    text-align: center;
    border-left: none;
    border-bottom: 3px solid transparent;
    border-radius: 4px;
  }

  .menu-link:hover,
  .menu-link.active {
    border-left-color: transparent;
    border-bottom-color: #A79277;
  }
}
</style> 