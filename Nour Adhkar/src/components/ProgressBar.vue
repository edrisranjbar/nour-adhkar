<template>
  <div class="progressbar-container">
    <div class="progressbar-fill" :style="{ width: `${progress}%` }"></div>
  </div>
</template>

<script>
export default {
  name: 'ProgressBar',
  props: {
    progress: {
      type: Number,
      required: true,
      validator: value => value >= 0 && value <= 100
    }
  }
}
</script>

<style scoped>
.progressbar-container {
  position: relative;
  height: 1.5rem;
  width: 180px;
  background-color: var(--brand-secondary);
  border-radius: 1rem;
  overflow: hidden;
  margin: 8px;
}

.progressbar-fill {
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  background-color: var(--brand-primary);
  border-radius: 4px;
  transition: width 0.3s ease;
  min-width: 5%;
}
</style> 