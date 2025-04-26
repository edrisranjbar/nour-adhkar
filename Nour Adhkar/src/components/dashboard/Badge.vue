<template>
  <div 
    class="flex flex-col items-center justify-center p-4 rounded-lg shadow-sm transition-all duration-300 hover:shadow-md border"
    :style="{ backgroundColor: badge.color + '10', borderColor: badge.color }"
  >
    <div class="text-3xl mb-2" :style="{ color: badge.color }">
      <i :class="badge.icon || 'fa-solid fa-award'"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ badge.name || 'نشان' }}</h3>
    <p class="text-sm text-gray-600 text-center">{{ badge.description || 'توضیحات نشان' }}</p>
    <div v-if="badge.earned_at" class="mt-2 text-xs text-gray-500">
      دریافت شده در {{ formatDate(badge.earned_at) }}
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'Badge',
  props: {
    badge: {
      type: Object,
      required: true,
      validator: (value) => {
        return value && typeof value === 'object'
      }
    }
  },
  setup(props) {
    console.log('Badge props:', props.badge) // Debug log

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('fa-IR')
    }

    return {
      formatDate
    }
  }
}
</script>

<style scoped>
.badge {
  border: 1px solid;
}
</style> 