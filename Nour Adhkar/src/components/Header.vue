<template>
  <header>
    <h1>{{ title }}</h1>
    <p class="description">{{ description }}</p>
  </header>
</template>
<script>
export default {
  props: {
    title: String,
    description: String
  },
  name: 'Header'
}
</script>