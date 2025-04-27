<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="p-3 bg-primary-100 rounded-full">
            <FontAwesomeIcon :icon="['fas', 'fire']" class="text-primary-600 text-2xl" />
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">روزهای متوالی</h3>
            <p v-if="!loading" class="text-3xl font-bold text-primary-600">{{ streak }}</p>
            <div v-else class="h-8 w-16 bg-gray-200 rounded animate-pulse"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="p-3 bg-primary-100 rounded-full">
            <FontAwesomeIcon :icon="['fas', 'heart']" class="text-primary-600 text-2xl" />
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">امتیاز قلب</h3>
            <p v-if="!loading" class="text-3xl font-bold text-primary-600">{{ heartScore }}</p>
            <div v-else class="h-8 w-16 bg-gray-200 rounded animate-pulse"></div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="p-3 bg-primary-100 rounded-full">
            <FontAwesomeIcon :icon="['fas', 'trophy']" class="text-primary-600 text-2xl" />
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">امتیاز</h3>
            <p v-if="!loading" class="text-3xl font-bold text-primary-600">{{ score }}</p>
            <div v-else class="h-8 w-16 bg-gray-200 rounded animate-pulse"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

export default {
  name: 'UserStats',
  components: {
    FontAwesomeIcon
  },
  setup(props) {
    const store = useStore()
    const loading = ref(true)
    const user = computed(() => store.state.user)

    const streak = computed(() => user.value?.streak || 0)
    const heartScore = computed(() => user.value?.heart_score || 0)
    const score = computed(() => user.value?.score || 0)
    const userName = computed(() => {
      console.log('User state:', user.value) // Debug log
      return user.value?.name || 'کاربر'
    })

    // Watch for changes in the user state
    watch(() => store.state.user, (newUser) => {
      console.log('User state changed:', newUser) // Debug log
      if (newUser) {
        loading.value = false
      }
    }, { immediate: true })

    return {
      streak,
      heartScore,
      score,
      userName,
      loading,
      user
    }
  }
}
</script> 