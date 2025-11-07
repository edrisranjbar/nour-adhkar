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
  background-color: var(--admin-surface);
  border-radius: 10px;
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.35);
  border: 1px solid var(--admin-border);
}

.header-content h1 {
  margin: 0;
  font-size: 1.5rem;
  color: var(--admin-text);
  font-weight: 600;
}

.description {
  margin: 0.5rem 0 0;
  color: var(--admin-muted);
  font-size: 1rem;
}
</style> 