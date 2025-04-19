<template>
  <aside class="sidebar">
    <nav class="nav">
      <ul class="menu">
        <li v-for="item in items" :key="item.path" class="menu-item">
          <RouterLink :to="item.path" class="menu-link" active-class="active" :exact="item.exact">
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
</style> 