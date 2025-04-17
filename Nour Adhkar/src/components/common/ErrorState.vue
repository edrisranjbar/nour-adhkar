<template>
  <div class="error-state">
    <font-awesome-icon icon="fa-solid fa-exclamation-circle" />
    <p>{{ message || 'خطایی رخ داده است' }}</p>
    <button v-if="retry" @click="$emit('retry')" class="retry-button">
      <font-awesome-icon icon="fa-solid fa-redo" />
      تلاش مجدد
    </button>
  </div>
</template>

<script>
export default {
  name: 'ErrorState',
  props: {
    message: {
      type: String,
      default: ''
    },
    retry: {
      type: Boolean,
      default: true
    }
  },
  emits: ['retry']
}
</script>