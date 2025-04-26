<template>
  <div 
    class="relative flex flex-col items-center justify-center p-5 rounded-lg transition-all duration-300 hover:shadow-lg"
    :class="[
      badge.earned_at ? 'earned-badge' : 'locked-badge',
    ]"
  >
    <!-- Icon container with circle background -->
    <div 
      class="icon-container mb-3 flex items-center justify-center rounded-full w-16 h-16"
      :class="{ 
        'earned': badge.earned_at,
        'locked': !badge.earned_at
      }"
      :style="badge.earned_at ? { backgroundColor: badge.color } : {}"
    >
      <font-awesome-icon 
        :icon="getIconParts(badge.icon)"
        class="text-2xl"
        :class="badge.earned_at ? 'text-white' : 'text-gray-400'"
        v-if="getIconParts(badge.icon).length === 2"
      />
      <i v-else :class="[badge.icon || 'fa-solid fa-award', badge.earned_at ? 'text-white' : 'text-gray-400']"></i>
    </div>
    
    <!-- Badge information -->
    <h3 class="text-lg font-semibold mb-1"
        :class="badge.earned_at ? 'text-gray-900' : 'text-gray-500'"
    >{{ badge.name || 'نشان' }}</h3>
    
    <p class="text-sm text-center"
       :class="badge.earned_at ? 'text-gray-600' : 'text-gray-400'"
    >{{ badge.description || 'توضیحات نشان' }}</p>
    
    <!-- Earned date -->
    <div v-if="badge.earned_at" class="mt-2 text-xs text-green-600 font-semibold">
      دریافت شده در {{ formatDate(badge.earned_at) }}
    </div>
    
    <!-- Lock overlay for non-earned badges -->
    <div v-if="!badge.earned_at" class="absolute top-2 right-2 text-gray-300">
      <font-awesome-icon icon="lock" class="text-sm" />
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
    
    const getIconParts = (iconClass) => {
      if (!iconClass) return ['solid', 'award'];
      
      // Parse fa-solid fa-seedling format to ['solid', 'seedling']
      const parts = iconClass.split('fa-');
      if (parts.length >= 3) {
        const prefix = parts[1].trim();
        const name = parts[2].trim();
        return [prefix, name];
      }
      
      return ['solid', 'award']; // Default fallback
    }

    return {
      formatDate,
      getIconParts
    }
  }
}
</script>

<style scoped>
.earned-badge {
  border: 2px solid;
  border-color: v-bind('badge.color');
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.locked-badge {
  background-color: #f5f5f5;
  border: 2px solid #e0e0e0;
  opacity: 0.7;
}

.icon-container {
  transition: all 0.3s ease;
}

.icon-container.earned {
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.icon-container.locked {
  background-color: #e0e0e0;
}
</style> 