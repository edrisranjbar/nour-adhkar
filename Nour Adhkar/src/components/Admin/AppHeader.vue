<template>
  <header class="app-header">
    <div class="header-content">
      <h1>{{ title }}</h1>
      <p v-if="description" class="description">{{ description }}</p>
    </div>
  </header>
</template>

<script>
export default {
  name: 'AppHeader',
  props: {
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      default: ''
    }
  }
};
</script>

<style scoped>
.app-header {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.header-content h1 {
  margin: 0;
  font-size: 1.5rem;
  color: #333;
  font-weight: 600;
}

.description {
  margin: 0.5rem 0 0;
  color: #666;
  font-size: 1rem;
}

/* Dark mode styles */
body.dark-mode .app-header {
  background-color: #333;
}

body.dark-mode .header-content h1 {
  color: #eee;
}

body.dark-mode .description {
  color: #aaa;
}
</style> 