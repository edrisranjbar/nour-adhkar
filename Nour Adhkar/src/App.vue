<script>
import { RouterView } from 'vue-router'
import { mapGetters, mapActions } from 'vuex'

export default {
  components: {
    RouterView
  },
  computed: {
    ...mapGetters(['isAuthenticated'])
  },
  methods: {
    ...mapActions(['refreshUserData']),
    updateMetaTags(route) {
      if (route.meta && (route.meta.title || route.meta.description)) {
        this.$setMeta({
          title: route.meta.title,
          description: route.meta.description,
          url: `https://adhkar.ir${route.path}`
        });
      }
    }
  },
  watch: {
    $route(to) {
      this.updateMetaTags(to);
    }
  },
  created() {
    if (this.isAuthenticated) {
      this.refreshUserData();
    }
  },
  mounted() {
    this.updateMetaTags(this.$route);
  }
}
</script>

<template>
  <div class="app-container">
    <RouterView />
  </div>
</template>

<style>
@import './assets/css/dark-mode.css';

:root {
  --font-size-factor: 1;
}

.app-container {
  position: relative;
  min-height: 100vh;
}
</style>
