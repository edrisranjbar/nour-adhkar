<template>
  <div :class="[
    'backdrop-blur-sm rounded-3xl shadow-xl border p-6 lg:p-8 transition-all duration-300',
    isDarkMode 
      ? 'bg-gray-800/80 border-gray-700/50 shadow-black/40' 
      : 'bg-white/80 border-white/20'
  ]">
    <h2 :class="[
      'text-xl lg:text-2xl font-bold mb-6 flex items-center gap-3 transition-colors duration-300',
      isDarkMode ? 'text-gray-100' : 'text-gray-800'
    ]">
      <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
        <FontAwesomeIcon icon="fa-solid fa-chart-line" class="text-white text-lg" />
      </div>
      آمار من
    </h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
      <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900 dark:to-red-900 rounded-2xl overflow-hidden hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 border border-orange-100 dark:border-red-800">
        <div class="h-1 bg-gradient-to-r from-orange-500 to-red-500"></div>
        <div class="p-5">
          <div class="flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-sm font-medium text-orange-700 dark:text-orange-200 mb-1">روزهای متوالی</span>
              <div class="flex items-end gap-1">
                <span v-if="!loading" class="text-3xl font-bold text-orange-800 dark:text-orange-100">{{ streak }}</span>
                <div v-else class="h-8 w-16 bg-orange-200 rounded animate-pulse"></div>
                 <span class="text-xs text-orange-600 dark:text-orange-300 mb-1">روز</span>
              </div>
            </div>
            <div class="p-3 bg-gradient-to-br from-orange-500 to-red-500 text-white rounded-xl shadow-lg">
              <FontAwesomeIcon icon="fa-solid fa-fire" class="text-xl" />
            </div>
          </div>
           <div class="mt-3 w-full bg-orange-200 dark:bg-orange-800 rounded-full h-2">
             <div class="bg-gradient-to-r from-orange-500 to-red-500 dark:from-orange-400 dark:to-red-400 h-2 rounded-full transition-all duration-700" :style="{ width: `${Math.min(streak * 10, 100)}%` }"></div>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900 dark:to-teal-950 rounded-2xl overflow-hidden hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 border border-emerald-100 dark:border-teal-800">
        <div class="h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
        <div class="p-5">
          <div class="flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-sm font-medium text-emerald-700 dark:text-emerald-200 mb-1">کل اذکار انجام‌شده</span>
              <div class="flex items-end gap-1">
                <span v-if="!loading" class="text-3xl font-bold text-emerald-800 dark:text-emerald-100">{{ totalDhikrs }}</span>
                <div v-else class="h-8 w-16 bg-emerald-200 rounded animate-pulse"></div>
              </div>
            </div>
            <div class="p-3 bg-gradient-to-br from-emerald-500 to-teal-500 text-white rounded-xl shadow-lg">
              <FontAwesomeIcon icon="fa-solid fa-book" class="text-xl" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref, watch } from 'vue'
import { useStore } from 'vuex'
import { useSettingsStore } from '@/stores/settings'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faFire, faBook, faChartLine } from '@fortawesome/free-solid-svg-icons'

library.add(faFire, faBook, faChartLine)

export default {
  name: 'UserStats',
  components: {
    FontAwesomeIcon
  },
  setup() {
    const store = useStore()
    const settingsStore = useSettingsStore()
    const loading = ref(true)
    const user = computed(() => store.state.user)
    const isDarkMode = computed(() => settingsStore.darkMode)

    const streak = computed(() => user.value?.streak || 0)
    const totalDhikrs = computed(() =>
      user.value?.total_adhkar_completed ?? user.value?.total_dhikrs ?? 0
    )

    watch(() => store.state.user, (newUser) => {
      if (newUser) {
        loading.value = false
      }
    }, { immediate: true })

    return {
      streak,
      totalDhikrs,
      loading,
      isDarkMode
    }
  }
}
</script>
