<script>
import { RouterView } from 'vue-router'
import { useSettingsStore } from './stores/settings'

export default {
  components: {
    RouterView
  },
  mounted() {
    // Initialize settings from the Pinia store
    const settingsStore = useSettingsStore()
    settingsStore.init()
  }
}
</script>

<template>
  <RouterView />
</template>

<style>
@import './assets/css/dark-mode.css';

:root {
  --font-size-factor: 1;
}
</style>
